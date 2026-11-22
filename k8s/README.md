# ZYRA Enterprise Kubernetes (K8s) Architecture & Operations Guide

This directory contains production-grade Kubernetes manifests for deploying the ZYRA E-commerce platform with automated load balancing, service discovery, high availability, and horizontal pod autoscaling (HPA).

---

## Architecture Components

```
                   Internet / Users
                          │
                          ▼
            [ Cloud Load Balancer / SSL ]
                          │
                          ▼
             [ NGINX Ingress Controller ]
                   (ingress.yaml)
                          │
            ┌─────────────┴─────────────┐
            ▼                           ▼
    [ zyra-web Pod 1 ]          [ zyra-web Pod 2 ]   (Nginx Static + Reverse Proxy)
            │                           │
            └─────────────┬─────────────┘
                          │ (K8s Service Discovery: zyra-app-service:9000)
                          ▼
             ┌─────────────────────────┐
             │   zyra-app Deployment   │
             │   (PHP 8.3 FPM Pods)    │ (Autoscales 2 -> 10 pods via HPA)
             └────────────┬────────────┘
                          │
        ┌─────────────────┴─────────────────┐
        ▼                                   ▼
 [ redis-service:6379 ]             [ External Managed MySQL ]
 (Cache & Rate Limiting)            (Amazon RDS / DigitalOcean)
```

---

## 1. Service Discovery (CoreDNS)
Within the `zyra-production` namespace, every service discovers others automatically by DNS name:
- **Backend PHP-FPM**: `zyra-app-service.zyra-production.svc.cluster.local:9000`
- **Redis Cache**: `redis-service.zyra-production.svc.cluster.local:6379`
- **MySQL Database**: `mysql-service.zyra-production.svc.cluster.local:3306`

Even when pods crash, restart, or scale up to 10 instances with changing private IPs, CoreDNS updates routes in real-time with **zero downtime or manual configuration**.

---

## 2. Load Balancing
- **Ingress Controller**: Distributes incoming HTTP/HTTPS traffic to `zyra-web-service`.
- **Nginx Reverse Proxy Cluster**: Distributes PHP FastCGI execution to `zyra-app-service` using round-robin and least-connection balancing.

---

## 3. Horizontal Pod Autoscaler (HPA)
- **Minimum Pods**: 2 (Ensures high availability during deployments)
- **Maximum Pods**: 10 (Scales up during festive sales or high traffic)
- **Metrics**: Triggers scale-up when CPU exceeds 70% or Memory exceeds 80%.

---

## 4. How to Deploy

### Step 1: Create Namespace
```bash
kubectl apply -f k8s/namespace.yaml
```

### Step 2: Configure Secrets & ConfigMap
Update `k8s/secret.yaml` with your production keys, then apply:
```bash
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml
```

### Step 3: Deploy Redis Cache
```bash
kubectl apply -f k8s/redis.yaml
```

### Step 4: Deploy App & Web Clusters
```bash
kubectl apply -f k8s/app-deployment.yaml
kubectl apply -f k8s/nginx-deployment.yaml
```

### Step 5: Enable Ingress & Autoscaler
```bash
kubectl apply -f k8s/ingress.yaml
kubectl apply -f k8s/hpa.yaml
```

---

## 5. Verification Commands

```bash
# Check running pods in production namespace
kubectl get pods -n zyra-production

# Check autoscaler status & metrics
kubectl get hpa -n zyra-production

# View real-time application logs
kubectl logs -f deployment/zyra-app -n zyra-production -c php-fpm
```
