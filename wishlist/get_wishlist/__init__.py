import logging
import json
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Get wishlist function triggered')
    
    try:
        # Get user_id from query parameters or JSON body
        user_id = req.params.get('user_id')
        
        if not user_id:
            try:
                req_body = req.get_json()
                user_id = req_body.get('user_id')
            except ValueError:
                pass
        
        if not user_id:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "user_id required"
                }),
                status_code=400,
                mimetype="application/json"
            )
        
        logging.info(f'Retrieving wishlist for user: {user_id}')
        
        # For now, return a simple response
        # In production, this would query the database
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": f"Wishlist endpoint working for user {user_id}",
                "user_id": user_id,
                "items": [],
                "note": "Connect to database to retrieve actual wishlist items"
            }),
            status_code=200,
            mimetype="application/json"
        )
        
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": str(e)
            }),
            status_code=500,
            mimetype="application/json"
        )
