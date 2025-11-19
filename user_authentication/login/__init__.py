import logging
import json
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Login function triggered - NO DB VERSION')
    
    try:
        req_body = req.get_json()
        email = req_body.get('email')
        
        # Test response without database
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": "Function works! Email received: " + email,
                "note": "Database disabled for testing"
            }),
            status_code=200,
            mimetype="application/json"
        )
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        return func.HttpResponse(
            json.dumps({"success": False, "error": str(e)}),
            status_code=500,
            mimetype="application/json"
        )
