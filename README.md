# On-Score.io — B2B On-Chain Player Intelligence & Crypto Scoring Gateway

[![Tests](https://img.shields.io/badge/tests-30%20passed%20(142%20assertions)-brightgreen.svg)]()
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)]()
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)]()
[![License](https://img.shields.io/badge/License-MIT-green.svg)]()

**On-Score** is an enterprise-grade crypto scoring gateway and player intelligence engine designed for iGaming operators, fintech gateways, and Web3 crypto payment solutions. It evaluates wallet risk, identifies cluster attribution (e.g. Stake, Rollbit, BC.Game, Roobet, 1xBet, Binance, OKX, Tornado Cash), calculates risk scores (0–100), and classifies player segments into VIP High-Roller, Sybil/Bonus Hunter, Regular, or High Risk AML.

---

## ⚡ Key Features

- **Multi-Chain Wallet Intelligence**: Support for Ethereum (`ETH`), TRON (`TRX`), Solana (`SOL`), Bitcoin (`BTC`), and BSC (`BNB`).
- **Cluster & Entity Attribution**: Instant identification of wallet owner tags (Casinos, Mixers, CEXs, Bridges, Sanctioned entities).
- **Risk Scoring & Decision Engine**: Real-time 0–100 risk score with automated recommendations (`allow`, `manual_review`, `block`, `step_up_kyc`).
- **Player Segmentation**: Heuristic categorization (`vip_high_roller`, `regular`, `bonus_hunter`, `dormant`, `high_risk_aml`).
- **Developer API & OpenAPI 3.0**: Fully documented REST API (`/api/v1/score/wallet`, `/api/v1/score/batch`, `/api/v1/analytics/stats`).
- **Modern Dashboard & Admin Portal**: Interactive analytics, entity registry, transaction explorer, and API key management.

---

## 🛠️ Stack

- **Backend**: Laravel 11.x (PHP 8.4)
- **Database**: MySQL / MariaDB (or SQLite for local dev)
- **Queue & Cache**: Redis / Database
- **Frontend**: Blade, Tailwind CSS, ApexCharts, Lucide Icons
- **Documentation**: OpenAPI 3.0 Specification (`/public/openapi.yaml` & interactive docs at `/docs`)

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.4+ (with extensions: `pdo`, `mbstring`, `openssl`, `bcmath`, `curl`)
- Composer 2+
- MySQL or SQLite

### Local Installation

```bash
# Clone the repository
git clone https://github.com/SHENiiDEV/on-score-crypto.git
cd on-score-crypto

# Install PHP dependencies
composer install

# Copy environment file and configure
cp .env.example .env

# Generate application encryption key
php artisan key:generate

# Run migrations and seeders (demo data, entities, clusters)
php artisan migrate --seed

# Start the development server
php artisan serve --port=5555
```

Visit [http://localhost:5555](http://localhost:5555) in your browser.

---

## 🧪 Testing

Run test suite with PHPUnit:

```bash
php artisan test
```

---

## 🔐 API Authentication

All scoring API endpoints require a Bearer token in the `Authorization` header:

```bash
curl -X POST https://on-score.io/api/v1/score/wallet \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "chain": "ETH",
    "address": "0x28c6c06298d514db089934071355e5743bf21d60"
  }'
```

---

## 📄 License

This software is open-sourced under the [MIT license](LICENSE).
