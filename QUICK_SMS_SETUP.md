# Quick Setup: Africa's Talking Real SMS

## 🚀 Get Your FREE API Key (5 minutes)

### Step 1: Sign Up (FREE)

1. Go to **[https://africastalking.com](https://africastalking.com)**
2. Click **"Get Started Free"**
3. Fill in your details:
   - Name, Email, Phone
   - Choose "Kenya" as country
   - Verify your email

### Step 2: Get API Key

1. Login to your dashboard
2. Go to **"Settings"** → **"API Keys"**
3. Copy your **API Key** (starts with `key_`)

### Step 3: Update Your .env File

Open `c:\xampp\htdocs\project\.env` and replace:

```bash
AFRICASTALKING_API_KEY=your_api_key_here
```

With your actual API key:

```bash
AFRICASTALKING_API_KEY=key_1234567890abcdef...
```

### Step 4: Test SMS

1. Go to the SMS page in DRMS
2. Select a user (make sure they have a phone number)
3. Send a test message
4. **You'll get FREE credits** to test with!

## 📱 Phone Number Format

Make sure users in your system have phone numbers in one of these formats:

- `+254712345678` ✅
- `254712345678` ✅
- `0712345678` ✅ (will be converted)

## 💰 Cost After Free Credits

- **KES 1.00** per SMS (~$0.008 USD)
- Much cheaper than Twilio!
- Pay via M-Pesa when ready

## 🎯 You're Ready!

Once you add your API key, DRMS will send **REAL SMS** to actual phone numbers immediately. No more verification hassles!

**Need help?** Check the SMS logs page to see delivery status.
