<?php

namespace App\Exports;

use App\Models\Log;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AsistenciaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $fecha_inicio;
    protected $fecha_fin;
    protected $docente_id;
    protected $hora_inicio_m;
    protected $hora_fin_m;
    protected $hora_inicio_t;
    protected $hora_fin_t;
    protected $total_columns;

    public function __construct($fecha_inicio, $fecha_fin, $docente_id, $hora_inicio_m, $hora_fin_m, $hora_inicio_t, $hora_fin_t)
    {
        $this->fecha_inicio = $fecha_inicio ? Carbon::parse($fecha_inicio) : Carbon::now()->startOfMonth();
        $this->fecha_fin = $fecha_fin ? Carbon::parse($fecha_fin) : Carbon::now()->endOfMonth();
        $this->docente_id = $docente_id;
        $this->hora_inicio_m = $hora_inicio_m;
        $this->hora_fin_m = $hora_fin_m;
        $this->hora_inicio_t = $hora_inicio_t;
        $this->hora_fin_t = $hora_fin_t;
    }

    public function headings(): array
    {
        $period = CarbonPeriod::create($this->fecha_inicio, $this->fecha_fin);
        $days = [];
        foreach ($period as $date) {
            $days[] = $date->format('d/m');
        }

        $this->total_columns = count($days) + 1; // +1 for the name column
        return array_merge(['Docente / ID Bio'], $days);
    }

    public function collection()
    {
        $query = User::where('rol', 'docente');
        if ($this->docente_id && $this->docente_id != 'todos') {
            $query->where('biometric_id', $this->docente_id);
        }
        $docentes = $query->get();

        $logsQuery = Log::whereBetween('fecha', [$this->fecha_inicio->startOfDay(), $this->fecha_fin->endOfDay()]);
        $logs = $logsQuery->get();

        $period = CarbonPeriod::create($this->fecha_inicio, $this->fecha_fin);
        $data = collect();

        foreach ($docentes as $docente) {
            $row = [$docente->nombre . ' (' . $docente->biometric_id . ')'];
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $docenteLogs = $logs->where('usuario_id', $docente->biometric_id)
                                    ->filter(function($log) use ($dateStr) {
                                        return str_starts_with($log->fecha, $dateStr);
                                    })->sortBy('fecha');

                if ($docenteLogs->isEmpty()) {
                    $row[] = '-';
                    continue;
                }

                $morningLogs = collect();
                $afternoonLogs = collect();

                foreach ($docenteLogs as $log) {
                    $time = Carbon::parse($log->fecha)->format('H:i');
                    if ($time >= $this->hora_inicio_m && $time <= $this->hora_fin_m) {
                        $morningLogs->push($log);
                    } elseif ($time >= $this->hora_inicio_t && $time <= $this->hora_fin_t) {
                        $afternoonLogs->push($log);
                    }
                }

                $cellText = [];
                
                if ($morningLogs->isNotEmpty()) {
                    $first = $morningLogs->first();
                    $last = $morningLogs->last();
                    $orig = stripos($first->origen, 'remot') !== false ? '(R)' : '(H)';
                    $t1 = Carbon::parse($first->fecha)->format('H:i');
                    $t2 = Carbon::parse($last->fecha)->format('H:i');
                    $cellText[] = "Mañana: $orig " . ($t1 === $t2 ? $t1 : "$t1-$t2");
                }
                
                if ($afternoonLogs->isNotEmpty()) {
                    $first = $afternoonLogs->first();
                    $last = $afternoonLogs->last();
                    $orig = stripos($first->origen, 'remot') !== false ? '(R)' : '(H)';
                    $t1 = Carbon::parse($first->fecha)->format('H:i');
                    $t2 = Carbon::parse($last->fecha)->format('H:i');
                    $cellText[] = "Tarde: $orig " . ($t1 === $t2 ? $t1 : "$t1-$t2");
                }

                $row[] = empty($cellText) ? '-' : implode("\n", $cellText);
            }
            $data->push($row);
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        // Estilo General y Bordes
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Alinear a la izquierda la primera columna (Nombres)
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Estilo de la Cabecera (Fila 1)
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF212529'], // Dark Gris (Bootstrap text-dark)
            ],
        ]);

        // Colores Condicionales
        for ($row = 2; $row <= $highestRow; $row++) {
            for ($col = 2; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); $col++) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $cellValue = $sheet->getCell($cellAddress)->getValue();

                if ($cellValue !== null && $cellValue !== '-' && $cellValue !== '') {
                    $sheet->getStyle($cellAddress)->applyFromArray([
                        'font' => ['color' => ['argb' => 'FF105A32'], 'size' => 8], // Verde oscuro, tamaño más pequeño para encajar
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1E7DD']], // Fondo Verde claro
                    ]);
                } elseif ($cellValue === '-') {
                    $sheet->getStyle($cellAddress)->applyFromArray([
                        'font' => ['color' => ['argb' => 'FFDC3545'], 'bold' => true], // Rojo
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8D7DA']], // Fondo Rojo claro
                    ]);
                }
            }
        }

        return [];
    }
}
