# Africa's Talking SMS Setup Guide

This guide will help you set up real SMS functionality using Africa's Talking instead of Twilio.

## Why Africa's Talking?

✅ **FREE Testing** - Get free credits for testing  
✅ **Local Focus** - Designed for African markets (Kenya, Tanzania, etc.)  
✅ **No Phone Verification** - Send to any number immediately  
✅ **Competitive Pricing** - Lower costs than international providers  
✅ **Better Delivery** - Direct partnerships with local networks  

## Setup Steps

### 1. Create Account
1. Go to [https://africastalking.com](https://africastalking.com)
2. Click "Get Started Free"
3. Fill in your details and verify your email
4. You'll get **FREE credits** to test with!

### 2. Get API Credentials
1. Login to your dashboard
2. Go to "Settings" → "API Keys"
3. Copy your API Key
4. Your username will be "sandbox" for testing

### 3. Configure DRMS
1. Open your `.env` file in the project root
2. Add these lines:
```bash
# SMS Configuration
SMS_MODE=real

# Africa's Talking Configuration
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=your_api_key_here
AFRICASTALKING_FROM=DRMS
```

### 4. Switch SMS Mode
To enable real SMS, change your `.env` file:
```bash
SMS_MODE=real  # Use 'mock' for testing, 'real' for production
```

### 5. Test SMS Functionality
1. Go to the SMS page in DRMS
2. Select a user with a valid phone number
3. Send a test message
4. Check the SMS logs to see delivery status

## Phone Number Format

Africa's Talking accepts these formats:
- `+254712345678` (Preferred)
- `254712345678`
- `0712345678` (Will be converted to +254712345678)

## Pricing

### Sandbox (Free Testing)
- **Free credits** for testing
- Send to **any number** for testing
- Perfect for development

### Live Mode
- **KES 1.00** per SMS (~$0.008 USD)
- **Pay via M-Pesa** or bank transfer
- Volume discounts available

## Features Included

✅ **Delivery Reports** - Real-time delivery status  
✅ **Message History** - All SMS logged in DRMS  
✅ **Error Handling** - Clear error messages  
✅ **Cost Tracking** - See SMS costs in logs  
✅ **Multiple Networks** - Safaricom, Airtel, Telkom  

## Switch Between Modes

### Development Mode (Mock SMS)
```bash
SMS_MODE=mock
```
- No real SMS sent
- Messages logged for testing
- Perfect for development

### Production Mode (Real SMS)
```bash
SMS_MODE=real
```
- Real SMS sent via Africa's Talking
- Actual delivery to phones
- Uses your credits

## Troubleshooting

### Common Issues

**Error: "API key not configured"**
- Check your `.env` file has the correct API key
- Ensure no extra spaces around the API key

**Error: "Invalid phone number"**
- Use international format: +254712345678
- Remove any spaces or special characters

**SMS not delivered**
- Check phone number is valid and active
- Ensure you have sufficient credits
- Check SMS logs for error details

### Getting Help

1. Check the SMS logs page in DRMS for delivery status
2. Visit [Africa's Talking Documentation](https://developers.africastalking.com/)
3. Contact their support: [help@africastalking.com](mailto:help@africastalking.com)

## Migration from Twilio

If you were using Twilio before:

1. Change SMS_MODE to 'real' in `.env`
2. Add Africa's Talking credentials to `.env`
3. Test with a few messages
4. Monitor delivery in SMS logs

The DRMS system will automatically use Africa's Talking instead of Twilio.

## Production Deployment

For production:
1. Create a live Africa's Talking account (not sandbox)
2. Add credits to your account
3. Update `.env` with live credentials:
```bash
AFRICASTALKING_USERNAME=your_live_username
AFRICASTALKING_API_KEY=your_live_api_key
```
4. Set `SMS_MODE=real`

## Benefits Over Twilio

| Feature | Africa's Talking | Twilio |
|---------|------------------|--------|
| **Free Testing** | ✅ Free credits | ❌ Paid only |
| **Phone Verification** | ✅ Not required | ❌ Required for trial |
| **Local Networks** | ✅ Direct partnerships | ❌ Third-party routing |
| **Pricing** | ✅ ~$0.008/SMS | ❌ ~$0.075/SMS |
| **Local Support** | ✅ African time zones | ❌ US/EU time zones |
| **M-Pesa Payments** | ✅ Supported | ❌ Not supported |

Start with sandbox mode for free testing, then upgrade when ready for production!
