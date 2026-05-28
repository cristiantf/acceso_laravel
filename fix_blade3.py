import os
import re

views_dir = r"C:\xampp\htdocs\acceso_laravel\resources\views"

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith(".blade.php"):
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # fix selected@endif -> selected @endif
            content = content.replace("selected@endif", "selected @endif")
            
            # fix active@endif -> active @endif
            content = content.replace("active@endif", "active @endif")
            
            # fix d.id|string -> $d->id
            content = content.replace("d.id|string", "$d->id")
            
            # fix {{ $user->nombre if user else 'Desconocido' }}
            # Instead of regex, let's just do exact string replacement for the known cases
            content = content.replace("{{ $user->nombre if user else 'Desconocido' }}", "{{ $user ? $user->nombre : 'Desconocido' }}")
            content = content.replace("{{ $user->nombre if user else 'N/A' }}", "{{ $user ? $user->nombre : 'N/A' }}")
            
            # log.descripcion or '' -> $log->descripcion ?? ''
            content = content.replace("{{ $log->descripcion or '' }}", "{{ $log->descripcion ?? '' }}")
            content = content.replace("{{ $log->latitud or '' }}", "{{ $log->latitud ?? '' }}")
            content = content.replace("{{ $log->longitud or '' }}", "{{ $log->longitud ?? '' }}")
            content = content.replace("{{ $log->foto_path or '' }}", "{{ $log->foto_path ?? '' }}")
            
            # fix {{ $log->fecha.strftime('%Y-%m-%d %H:%M:%S') }}
            content = content.replace("{{ $log->fecha.strftime('%Y-%m-%d %H:%M:%S') }}", "{{ \\Carbon\\Carbon::parse($log->fecha)->format('Y-m-d H:i:s') }}")
            
            # fix {{ $log->fecha.strftime('%H:%M:%S') }}
            content = content.replace("{{ $log->fecha.strftime('%H:%M:%S') }}", "{{ \\Carbon\\Carbon::parse($log->fecha)->format('H:i:s') }}")

            with open(path, "w", encoding="utf-8") as f:
                f.write(content)
