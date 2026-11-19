# সাইট লাইভ করার গাইড (VPS)

এই ফোল্ডারের স্ক্রিপ্টগুলো দিয়ে একটা নতুন Ubuntu VPS-এ পুরো সাইট চালু হবে:
nginx + PHP 8.4 + MySQL 8 + Node 22, queue worker, scheduler আর firewall সহ।

| ফাইল | কোথায় চলে | কাজ |
|---|---|---|
| `provision.sh` | সার্ভারে, একবার | সফটওয়্যার ইনস্টল, ডাটাবেস তৈরি, কোড ডাউনলোড ও বিল্ড, nginx চালু |
| `upload-data.ps1` | আপনার PC-তে, একবার | লোকাল ডাটাবেস (শুধু শপের ৪৯টি টেবিল, WordPress `wp_*` বাদে) আর প্রোডাক্টের ছবি সার্ভারে পাঠায় |
| `import-data.sh` | সার্ভারে (upload-data নিজেই চালায়) | ডাটা ইমপোর্ট, ছবির লিংক ঠিক করা, ডিফল্ট পাসওয়ার্ড বদলানো |
| `update.sh` | সার্ভারে, প্রতিবার আপডেটের সময় | GitHub থেকে নতুন কোড এনে রিবিল্ড |
| `fix-image-urls.sh` | সার্ভারে, ডোমেইন বদলালে | পুরনো ঠিকানার ছবির লিংক ঠিক করে |

## ধাপ ০: যা লাগবে

1. **VPS**: Ubuntu 24.04 (বা 22.04), কমপক্ষে 2 GB RAM (DigitalOcean, Hostinger VPS, Contabo, Vultr ইত্যাদি)। কেনার পর **IP অ্যাড্রেস** আর **root পাসওয়ার্ড** পাবেন।
2. **GitHub টোকেন**: রিপোজিটরিটা private, তাই সার্ভারকে কোড ডাউনলোডের অনুমতি দিতে হবে।
   GitHub → Settings → Developer settings → Fine-grained tokens → *Generate new token* →
   Repository access: শুধু `Cloth` → Permissions: **Contents: Read-only**।
3. সর্বশেষ কোড GitHub-এ push করা থাকতে হবে (`git push`)।

## ধাপ ১: সার্ভার সেটআপ (~১০ মিনিট)

আপনার PC-র PowerShell থেকে সার্ভারে ঢুকুন:

```powershell
ssh root@<SERVER_IP>
```

সার্ভারে এই কমান্ডগুলো চালান (`<TOKEN>` এর জায়গায় GitHub টোকেন দিন):

```bash
export GIT_TOKEN=<TOKEN>
curl -fsSL -H "Authorization: Bearer $GIT_TOKEN" \
  https://raw.githubusercontent.com/shadowrahmat/Cloth/main/deploy/provision.sh -o provision.sh
REPO_URL=https://github.com/shadowrahmat/Cloth.git bash provision.sh
```

শেষে `Site is live at http://<SERVER_IP>` লেখা দেখাবে। তারপর `exit` দিয়ে বেরিয়ে আসুন।

## ধাপ ২: ডাটা আর ছবি আপলোড

আপনার PC-তে XAMPP-এর MySQL চালু রেখে, প্রজেক্ট ফোল্ডার (`D:\Cloth`) থেকে:

```powershell
powershell -ExecutionPolicy Bypass -File deploy\upload-data.ps1 -Server <SERVER_IP>
```

দুইবার সার্ভারের পাসওয়ার্ড চাইবে। শেষে **নতুন অ্যাডমিন পাসওয়ার্ড** প্রিন্ট করবে। এটা অবশ্যই সেভ করুন,
পুরনো `password` আর কাজ করবে না। (সার্ভারের `/root/cloth-credentials.txt` ফাইলেও রাখা থাকে।)

> ⚠️ এই স্ক্রিপ্ট লাইভ ডাটাবেস **মুছে** আপনার লোকাল ডাটা বসায়। শুধু প্রথমবার লাইভ করার সময় চালাবেন।
> লাইভ সাইটে আসল অর্ডার আসা শুরু হলে আর চালাবেন না।

## ধাপ ৩: চেক করুন

- `http://<SERVER_IP>` — স্টোরফ্রন্ট, প্রোডাক্টের ছবি দেখা যাচ্ছে কিনা
- `http://<SERVER_IP>/admin` — নতুন পাসওয়ার্ড দিয়ে লগইন

## পরে কোড আপডেট করতে

PC থেকে `git push` করার পর:

```bash
ssh root@<SERVER_IP> "bash /var/www/cloth/deploy/update.sh"
```

## ডোমেইন কেনার পর

1. ডোমেইনের DNS-এ একটা **A record** দিন: `@` → `<SERVER_IP>` (চাইলে `www` → `<SERVER_IP>`)।
2. DNS কাজ শুরু করলে (কয়েক মিনিট থেকে কয়েক ঘণ্টা) সার্ভারে:

```bash
cd /var/www/cloth
sed -i 's|^APP_URL=.*|APP_URL=https://yourdomain.com|' .env
sed -i 's|server_name _;|server_name yourdomain.com www.yourdomain.com;|' /etc/nginx/sites-available/cloth
systemctl reload nginx
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com   # ফ্রি HTTPS
bash deploy/fix-image-urls.sh http://<SERVER_IP>          # IP দিয়ে আপলোড করা ছবির লিংক ঠিক করে
php artisan optimize
```

## সমস্যা হলে

- এরর লগ: `tail -50 /var/www/cloth/storage/logs/laravel-$(date +%F).log`
- nginx লগ: `tail -50 /var/log/nginx/error.log`
- সার্ভিস রিস্টার্ট: `systemctl restart php8.4-fpm nginx cloth-queue`
