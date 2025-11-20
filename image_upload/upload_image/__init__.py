import logging
import json
import os
import base64
import uuid
from datetime import datetime
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Upload image function triggered')
    
    try:
        # Get the file from the request
        req_body = req.get_json()
        
        if not req_body or 'image' not in req_body or 'filename' not in req_body:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "image and filename required"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        # Get base64 image data and filename
        image_data = req_body.get('image')
        original_filename = req_body.get('filename')
        
        logging.info(f'Uploading image: {original_filename}')
        
        # Use original filename
        unique_filename = original_filename
        
        # Decode base64 image
        try:
            # Remove data URL prefix if present
            if ',' in image_data:
                image_data = image_data.split(',')[1]
            image_bytes = base64.b64decode(image_data)
        except Exception as e:
            logging.error(f'Failed to decode image: {str(e)}')
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": f"Invalid image data: {str(e)}"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        # Upload to Azure Blob Storage using REST API
        try:
            import requests
            from datetime import datetime as dt
            from hashlib import sha256
            import hmac
            
            storage_account = os.environ.get('STORAGE_ACCOUNT_NAME', '')
            storage_key = os.environ.get('STORAGE_ACCOUNT_KEY', '')
            
            if not storage_account or not storage_key:
                raise Exception("Storage account credentials not configured")
            
            container_name = "images"
            
            # Build blob URL
            blob_url = f"https://{storage_account}.blob.core.windows.net/{container_name}/{unique_filename}"
            
            # Prepare headers for blob upload
            date_str = dt.utcnow().strftime('%a, %d %b %Y %H:%M:%S GMT')
            content_length = len(image_bytes)
            
            # Build string to sign
            string_to_sign = f"PUT\n\n\n{content_length}\n\nimage/jpeg\n\n\n\n\n\n\nx-ms-blob-type:BlockBlob\nx-ms-date:{date_str}\nx-ms-version:2021-08-06\n/{storage_account}/{container_name}/{unique_filename}"
            
            # Sign the request
            key_bytes = base64.b64decode(storage_key)
            signature = base64.b64encode(hmac.new(key_bytes, string_to_sign.encode('utf-8'), sha256).digest()).decode()
            
            headers = {
                'x-ms-date': date_str,
                'x-ms-version': '2021-08-06',
                'x-ms-blob-type': 'BlockBlob',
                'Content-Type': 'image/jpeg',
                'Content-Length': str(content_length),
                'Authorization': f'SharedKey {storage_account}:{signature}'
            }
            
            # Upload the blob
            response = requests.put(blob_url, headers=headers, data=image_bytes)
            
            if response.status_code in [200, 201]:
                logging.info(f'Image uploaded successfully: {blob_url}')
                
                return func.HttpResponse(
                    json.dumps({
                        "success": True,
                        "message": "Image uploaded successfully",
                        "url": blob_url,
                        "filename": unique_filename
                    }),
                    status_code=200,
                    mimetype="application/json",
                    headers={
                        "Access-Control-Allow-Origin": "*",
                        "Access-Control-Allow-Methods": "POST, OPTIONS",
                        "Access-Control-Allow-Headers": "Content-Type"
                    }
                )
            else:
                raise Exception(f"Upload failed: {response.status_code} - {response.text}")
            
        except Exception as e:
            logging.error(f'Storage upload failed: {str(e)}')
            import traceback
            logging.error(traceback.format_exc())
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": f"Storage upload error: {str(e)}"
                }),
                status_code=500,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
    except ValueError as e:
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": "Invalid JSON body"
            }),
            status_code=400,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "POST, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type"
            }
        )
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        import traceback
        logging.error(traceback.format_exc())
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": str(e)
            }),
            status_code=500,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "POST, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type"
            }
        )
