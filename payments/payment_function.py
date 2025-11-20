import azure.functions as func
import logging
import json
import hashlib
import time
from datetime import datetime

app = func.FunctionApp()

@app.function_name(name="ProcessPayment")
@app.route(route="process_payment", methods=["POST"], auth_level=func.AuthLevel.ANONYMOUS)
def process_payment(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Payment processing function triggered.')

    try:
        # Get request body
        req_body = req.get_json()
        
        # Validate required fields
        order_number = req_body.get('order_number')
        amount = req_body.get('amount')
        payment_method = req_body.get('payment_method')
        user_id = req_body.get('user_id')
        
        if not all([order_number, amount, payment_method, user_id]):
            return func.HttpResponse(
                json.dumps({
                    'success': False,
                    'message': 'Missing required fields: order_number, amount, payment_method, user_id'
                }),
                status_code=400,
                mimetype="application/json"
            )
        
        # Validate amount
        try:
            amount = float(amount)
            if amount <= 0:
                raise ValueError("Amount must be positive")
        except (ValueError, TypeError):
            return func.HttpResponse(
                json.dumps({
                    'success': False,
                    'message': 'Invalid amount'
                }),
                status_code=400,
                mimetype="application/json"
            )
        
        # Validate payment method
        valid_methods = ['credit_card', 'debit_card', 'paypal', 'klarna']
        if payment_method not in valid_methods:
            return func.HttpResponse(
                json.dumps({
                    'success': False,
                    'message': f'Invalid payment method. Must be one of: {", ".join(valid_methods)}'
                }),
                status_code=400,
                mimetype="application/json"
            )
        
        # Generate transaction ID
        timestamp = str(time.time())
        hash_input = f"{order_number}{user_id}{timestamp}".encode()
        transaction_id = f"txn_{hashlib.sha256(hash_input).hexdigest()[:12]}"
        
        # Log payment processing
        logging.info(f'Processing payment for order {order_number}')
        logging.info(f'Amount: £{amount:.2f}, Method: {payment_method}, User: {user_id}')
        
        # Simulate payment processing
        # In a real scenario, this would integrate with a payment gateway like Stripe, PayPal, etc.
        
        # For demo purposes, always succeed (you can add logic to simulate failures)
        response_data = {
            'success': True,
            'transaction_id': transaction_id,
            'order_number': order_number,
            'amount': amount,
            'payment_method': payment_method,
            'status': 'completed',
            'message': 'Payment processed successfully',
            'timestamp': datetime.utcnow().isoformat()
        }
        
        logging.info(f'Payment successful: {transaction_id}')
        
        return func.HttpResponse(
            json.dumps(response_data),
            status_code=200,
            mimetype="application/json"
        )
        
    except ValueError as e:
        logging.error(f'Invalid JSON in request: {str(e)}')
        return func.HttpResponse(
            json.dumps({
                'success': False,
                'message': 'Invalid JSON format'
            }),
            status_code=400,
            mimetype="application/json"
        )
    except Exception as e:
        logging.error(f'Payment processing error: {str(e)}')
        return func.HttpResponse(
            json.dumps({
                'success': False,
                'message': 'Payment processing failed',
                'error': str(e)
            }),
            status_code=500,
            mimetype="application/json"
        )
