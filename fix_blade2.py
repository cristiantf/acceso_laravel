import os
import re

views_dir = r"C:\xampp\htdocs\acceso_laravel\resources\views"

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith(".blade.php"):
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # fix {% extends "base.html" %} -> @extends('base')
            content = re.sub(r'\{%\s*extends\s*"([^"]+)\.html"\s*%\}', r"@extends('\1')", content)
            
            # fix {% block xyz %} -> @section('xyz')
            content = re.sub(r'\{%\s*block\s+([a-zA-Z0-9_]+)\s*%\}', r"@section('\1')", content)
            
            # fix {% endblock %} -> @endsection
            content = re.sub(r'\{%\s*endblock\s*%\}', r"@endsection", content)
            
            # fix @foreach ... @else ... @endforeach -> @forelse ... @empty ... @endforelse
            # This is multiline so we need a loop or complex regex.
            # simpler regex since we know the pattern:
            while True:
                new_content = re.sub(r'@foreach\s*\((.*?)\)(.*?)@else(.*?)@endforeach', r'@forelse(\1)\2@empty\3@endforelse', content, flags=re.DOTALL)
                if new_content == content:
                    break
                content = new_content
            
            # fix python 'and' / 'or' inside @if
            # This is tricky but let's just do it for the known ones:
            # @if($log->origen == 'Asistencia remota' and (log.latitud or log.foto_path or log.descripcion))
            content = content.replace(" and (log.latitud or log.foto_path or log.descripcion)", " && ($log->latitud || $log->foto_path || $log->descripcion)")
            
            with open(path, "w", encoding="utf-8") as f:
                f.write(content)
