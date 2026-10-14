    # Deployment Manual: DigitalOcean Droplet (Cost-Effective)

This guide walks you through deploying your Laravel application on a $6/mo DigitalOcean Droplet using Docker Compose.

## Prerequisites
1. **GitHub Repository**: Ensure your code is pushed to GitHub.
2. **DigitalOcean Account**: Create one if you haven't.

## Step 1: Create the Droplet
1. Go to DigitalOcean Console -> **Create** -> **Droplets**.
2. **Region**: Choose the one closest to your users.
3. **Image**: Choose **Docker** from the "Marketplace" tab (it comes with Docker pre-installed).
   - Alternatively, choose **Ubuntu 24.04** and install Docker manually.
4. **Size**: **Basic**, **Regular App**, **$6/mo** (1GB RAM, 1 CPU). 
   - *Note: If build fails due to memory, you might need to add swap space (see below).*
5. **Authentication**: Add your SSH Key.
6. **Create Droplet**.

## Step 2: Connect and Setup
Open your terminal and SSH into the droplet:
```bash
ssh root@<your-droplet-ip>
```

### 1. Clone your Repository
```bash
git clone https://github.com/abdelrahman-waziry/mezatech-admin.git app
cd app
```

### 2. Setup Environment Variables
Create the `.env` file based on `.env.example`:
```bash
cp .env.example .env
nano .env
```
Update the following values in `.env`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` (generate with `php artisan key:generate --show`, then set it before starting containers)
- `APP_URL=https://<your-domain>`
- `DOMAIN_NAME=your-domain`
- `DB_CONNECTION=mysql`
- `DB_HOST=db` (matches the service name in docker-compose)
- `DB_DATABASE=mezatech_dashboard`
- `DB_USERNAME=mezatech`
- `DB_PASSWORD=your_secure_database_password`
- `DB_ROOT_PASSWORD=your_secure_root_password`
- `BEST_REPAIR_API_BASE_URL=https://bestrepairegypt.com/v1`

### 3. Add Swap Space (Crucial for 1GB Droplets)
1GB RAM might be too low for `npm run build` or Composer. Add 2GB swap:
```bash
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' | tee -a /etc/fstab
```

## Step 3: Deployment
Run the application using the production compose file:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```
- The `app` container runs migrations once. The worker and scheduler do not run migrations.
- `storage:link` and Laravel config/route/view caches are prepared during startup.

After the first deployment, import the catalog once:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=CatalogDataSeeder --force
```

The catalog seeder is upsert-based and does not delete production rows, but run it only when importing a new catalog snapshot.

Create the first Filament administrator manually or through a controlled release procedure. Do not use the development default-password seeder in production.

## Step 4: Verification
Visit `https://<your-domain>` in your browser.

## Updates / New Deployments
To deploy changes:
```bash
cd app
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

Check the deployment:
```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs --tail=100 app
docker compose -f docker-compose.prod.yml exec app php artisan about
```

## Troubleshooting
- **Logs**: `docker compose -f docker-compose.prod.yml logs -f app`
- **Shell Access**: `docker compose -f docker-compose.prod.yml exec app bash`
