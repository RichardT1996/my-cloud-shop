import logging
import json
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Remove from wishlist function triggered')
    
    try:
        req_body = req.get_json()
        user_id = req_body.get('user_id')
        watch_id = req_body.get('watch_id')
        
        if not user_id or not watch_id:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "user_id and watch_id required"
                }),
                status_code=400,
                mimetype="application/json"
            )
        
        logging.info(f'Removing watch {watch_id} from wishlist for user {user_id}')
        
        # For now, return a simple response
        # In production, this would delete from the database
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": "Item removed from wishlist",
                "user_id": user_id,
                "watch_id": watch_id,
                "note": "Connect to database to actually remove item"
            }),
            status_code=200,
            mimetype="application/json"
        )
        
    except ValueError:
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": "Invalid JSON body"
            }),
            status_code=400,
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
