import os
import re

views_dir = r"C:\xampp\htdocs\acceso_laravel\resources\views"

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith(".blade.php"):
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # fix foreach
            def repl_foreach(m):
                iterable = m.group(1).strip()
                vars_part = m.group(2).strip()
                if not iterable.startswith('$'): iterable = '$' + iterable
                if ',' in vars_part:
                    parts = vars_part.split(',')
                    k = parts[0].strip()
                    v = parts[1].strip()
                    if not k.startswith('$'): k = '$' + k
                    if not v.startswith('$'): v = '$' + v
                    vars_part = f"{k} => {v}"
                else:
                    if not vars_part.startswith('$'): vars_part = '$' + vars_part
                
                return f"@foreach({iterable} as {vars_part})"
                
            content = re.sub(r'@foreach\((.*?)\s+as\s+(.*?)\)', repl_foreach, content)
            
            # fix {{ var.prop }}
            content = re.sub(r'\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\.', r'{{ $\1->', content)
            
            # fix @if(var.prop == x)
            content = re.sub(r'@if\(([a-zA-Z_][a-zA-Z0-9_]*)\.', r'@if($\1->', content)

            # fix togglePermiso('{{d.id}}'
            content = re.sub(r'togglePermiso\(\'\{\{([a-zA-Z_][a-zA-Z0-9_]*)\.', r"togglePermiso('{{$\1->", content)
            
            # handle |length
            content = re.sub(r'\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\|length\s*\}\}', r'{{ count($\1) }}', content)
            
            # fix url_for with args
            content = re.sub(r'\(\'([a-zA-Z_]+)\',\s*id=([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_]+)\)', r"('\1', ['id' => $\2->\3])", content)

            # fix request.endpoint
            content = content.replace("request.endpoint", "request()->route()->getName()")

            with open(path, "w", encoding="utf-8") as f:
                f.write(content)
