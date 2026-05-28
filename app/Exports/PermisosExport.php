<?php

namespace App\Exports;

use App\Models\Permiso;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PermisosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $fecha_inicio;
    protected $fecha_fin;
    protected $docente_id;

    public function __construct($fecha_inicio, $fecha_fin, $docente_id)
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->docente_id = $docente_id;
    }

    public function query()
    {
        $query = Permiso::query()->with('docente');

        if ($this->fecha_inicio) {
            $query->whereDate('fecha_permiso', '>=', $this->fecha_inicio);
        }
        if ($this->fecha_fin) {
            $query->whereDate('fecha_permiso', '<=', $this->fecha_fin);
        }
        if ($this->docente_id && $this->docente_id != 'todos') {
            $query->where('user_id', $this->docente_id);
        }

        return $query->orderBy('fecha_permiso', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID Permiso',
            'Docente',
            'Fecha del Permiso',
            'Observación',
            'Fecha de Registro',
        ];
    }

    public function map($permiso): array
    {
        return [
            $permiso->id,
            $permiso->docente ? $permiso->docente->nombre : 'Desconocido',
            $permiso->fecha_permiso,
            $permiso->observacion,
            $permiso->created_at,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilos para la cabecera (Fila 1)
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0D6EFD'], // Azul Bootstrap primario
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Bordes para toda la tabla
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDDDDDD'],
                ],
            ],
        ]);
        
        // Centrar las fechas
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
