import os
import re

views_dir = r"C:\xampp\htdocs\acceso_laravel\resources\views"

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith(".blade.php"):
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # fix checked@endif -> checked @endif
            content = content.replace("checked@endif", "checked @endif")
            
            # fix @section('body_class')no-sidebar@endsection -> @section('body_class') no-sidebar @endsection
            content = content.replace("@section('body_class')no-sidebar@endsection", "@section('body_class') no-sidebar @endsection")
            
            # fix route(gestion_asistencia) -> route('gestion_asistencia')
            content = content.replace("route(gestion_asistencia)", "route('gestion_asistencia')")
            
            # fix route(admin_dashboard) -> route('admin_dashboard')
            content = content.replace("route(admin_dashboard)", "route('admin_dashboard')")
            
            # fix route(gestion_permisos) -> route('gestion_permisos')
            content = content.replace("route(gestion_permisos)", "route('gestion_permisos')")

            with open(path, "w", encoding="utf-8") as f:
                f.write(content)
