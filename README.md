
# Payment Gateway Demo

This is a PHP 8.1 application demonstrating a **payment gateway** with:
- A **Merchant** entity stored via **Doctrine ORM**.
- Automatic generation of a **random 16-hex auth token** on merchant creation.
- **Endpoints** for merchant creation, PSP updates, and charging a card.
- **Docker** for containerization (PHP + MariaDB + optional Adminer).
- **Stripe** and **Pin Payments** PSP integrations.
- **Tests** (PHPUnit) for both domain logic (use cases, auth) and controller endpoints.

---

## 1. Architecture

- **Domain**: Contains the `Merchant` entity and `MerchantRepositoryInterface`.
- **Doctrine**: Implementation of the domain interface (`DoctrineMerchantRepository`) to store merchants in a `merchants` table.
- **Controllers**:
  - **MerchantController**: Handles creating a new merchant (without requiring auth) and updating the merchant’s PSP (requires auth).
  - **PaymentController**: Handles charging a card (requires auth).
- **Routing**: 
  - Defined in `app/Infrastructure/Framework/Routing/routes.php` using `[ControllerClass::class, 'methodName']`.
  - `public/index.php` matches the request method + path, instantiates the indicated controller, and calls the method.
- **AuthService**: Parses `"Bearer <token>"` from the request header and finds the corresponding `Merchant`.
- **PSP** Integrations:
  - **StripePaymentService** and **PinPaymentService** implement a `PaymentServiceInterface` for charging.
- **PSPRegistration** (Optional): If needed to handle retrieving or validating PSP API keys upon creating a merchant.

---

## 2. How to Build & Run

1. **Clone** or copy all these files into a local directory.
2. Ensure **Docker** and **Docker Compose** are installed.
3. **Build and start** the Docker image:
   ```bash
   docker-compose up --build
4.  **Create** the `merchants` table (first run only):
    ```bash
    docker-compose exec payment-app php vendor/bin/doctrine orm:schema-tool:create
    ```
    This uses Doctrine’s schema tool to create tables based on the Entity definitions.

The application is available at http://localhost:8000.  
You can browse the DB(**Adminer**) at [http://localhost:8080](http://localhost:8080) (System: MySQL, Server: `mariadb`, User: `root`, Password: `secret`, DB: `payments_db`).


## 3. How to Use (Endpoints)

### 3.1 Create a Merchant

-   **`POST /merchant/create`**
-   **No authentication required**
-   **Request Body** example:
    ```json
    {
      "psp": "stripe",
      "psp_api_key": "pk_test_51QrNwFRpVQ5XJDdcUvuRUIbMsEqFvNYIdseVWAmrsV5cKT09biRvTaOxBfNf2H8j3xgKnBEuQwA3CVYbnjvy4Zft000ht56Axh"
    }
    or
    {
      "psp": "pin_payments",
      "psp_api_key": "test-pin-api-key" // I wasn't able to create a sandbox account in this PSP
    }
    ```
    
    -   `psp`: The PSP name, e.g. `"stripe"` or `"pin_payments"`.
    -   `psp_api_key`: The merchant’s actual API key for that PSP.
-   **Response**: JSON with `merchant_id` (database auto-increment) and `auth_token` (a random 16-char hex string).  
    Store this token because you’ll need it to authenticate future requests.

### 3.2 Update PSP

-   **`POST /merchant/update-psp`**
-   **Requires**: `Authorization: Bearer <auth_token>`
-   **Request Body** example:
    
    ```json{
      "merchant_id": 1,
      "new_psp": "pin_payments"
    }
    ```
    
    -   Must match the authenticated merchant’s ID, or you’ll get `403`.
-   **Response**: JSON with a success message if updated.

### 3.3 Charge a Card

-   **`POST /charge`**
-   **Requires**: `Authorization: Bearer <auth_token>`
-   **Request Body** example:
    
    ```json
    {
      "card_number": "4242424242424242",
      "expiration_date": "12/25",
      "cvv": "123",
      "cardholder_name": "John Doe",
      "amount": 20.5
    }
    ```
    
-   **Response**: JSON with a `"status"`, `"transaction_id"`, etc. from the PSP.

----------

## 4. Running Tests

We use **PHPUnit** (version 9.x). Our test suite covers:

-   **AuthService**: Checking `"Bearer"` token parsing.
-   **PaymentUseCase**: Orchestrating a payment with a PSP.
-   **MerchantController**: Creating merchants (no auth), updating PSP (auth).
-   **PaymentController**: Charging a card (auth).

Run all tests:

```bash
docker-compose exec payment-app vendor/bin/phpunit --testdox tests`
```

You should see the results for the 10 tests (or however many you’ve added).

----------

## 5. What is Missing / Production Considerations

This code is a **demonstration**. It is not production-grade. Missing elements include:

1.  **Robust Error Handling**: We do minimal try/catch. A real system would have a centralized error handler or exceptions mapped to HTTP statuses.
2.  **Logging / Monitoring**: Production payment gateways typically use comprehensive logging, monitoring, alerts, etc.
3.  **Security**:
    -   Token-based auth here is simplistic (a raw hex token).
    -   A real system might use secure token storage, JWT, OAuth, or a more robust approach.
    -   TLS/HTTPS setup is also critical in production.
4.  **Doctrine Migrations**: Instead of `orm:schema-tool:create`, you would use **Doctrine Migrations** to version your schema changes over time.
5.  **PSP Onboarding**: We rely on a user-provided `psp_api_key`. Real systems might do OAuth flows, callback confirmations, or store credentials differently.
6.  **Automated Infrastructure**: For production, you’d have staging, CI/CD pipelines, load balancers, SSL termination, etc.
7.  **Validation**: We do basic checks on input fields, but real systems might have more thorough validation (e.g., Luhn checks, date format checks, etc.).

Overall, this repository is meant to showcase a **Clean Architecture** approach with **SOLID** design, **Doctrine** for persistence, and PSP integrations with Docker-based deployment.