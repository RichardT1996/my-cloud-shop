import logging
import json
import os
import sys
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Diagnostic function triggered')
    
    diagnostics = {
        "python_version": sys.version,
        "python_path": sys.path,
        "environment_variables": {
            "DB_SERVER": os.environ.get('DB_SERVER', 'NOT SET'),
            "DB_NAME": os.environ.get('DB_NAME', 'NOT SET'),
            "DB_USER": os.environ.get('DB_USER', 'NOT SET'),
            "DB_PASS_SET": "YES" if os.environ.get('DB_PASS') else "NO"
        },
        "installed_packages": [],
        "pyodbc_status": "not_checked"
    }
    
    # Try to list installed packages
    try:
        import pkg_resources
        installed = [f"{pkg.key}=={pkg.version}" for pkg in pkg_resources.working_set]
        diagnostics["installed_packages"] = sorted(installed)
    except Exception as e:
        diagnostics["installed_packages_error"] = str(e)
    
    # Try to import pyodbc
    try:
        import pyodbc
        diagnostics["pyodbc_status"] = "imported_successfully"
        diagnostics["pyodbc_version"] = pyodbc.version
        
        # Try to list available drivers
        try:
            drivers = pyodbc.drivers()
            diagnostics["odbc_drivers"] = drivers
        except Exception as e:
            diagnostics["odbc_drivers_error"] = str(e)
            
    except ImportError as e:
        diagnostics["pyodbc_status"] = f"import_failed: {str(e)}"
    except Exception as e:
        diagnostics["pyodbc_status"] = f"error: {str(e)}"
    
    return func.HttpResponse(
        json.dumps(diagnostics, indent=2),
        status_code=200,
        mimetype="application/json"
    )
