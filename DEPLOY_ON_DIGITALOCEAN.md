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
git clone https://github.com/your-username/your-repo.git app
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
- `APP_URL=http://<your-droplet-ip>`
- `DB_CONNECTION=mysql`
- `DB_HOST=db` (matches the service name in docker-compose)
- `DB_PASSWORD=your_secure_password`

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
- `-d`: Detached mode (runs in background).
- `--build`: Forces a rebuild of the Docker image.

## Step 4: Verification
Visit `http://<your-droplet-ip>` in your browser.

## Updates / New Deployments
To deploy changes:
```bash
cd app
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

## Troubleshooting
- **Logs**: `docker compose -f docker-compose.prod.yml logs -f app`
- **Shell Access**: `docker compose -f docker-compose.prod.yml exec app bash`
