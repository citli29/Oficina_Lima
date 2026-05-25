#   Structure 

# /api
## app actions
    /auth/register -> POST
    /auth/login -> POST
    /auth/logout -> POST

## db actions
    /users
    /users/{id}
    /schedules
    /schedules/{id}
    /clients
    /clients/{id}
    /makes/
    /makes/{id}
    /makes/{id}/models
    /makes/{id}/models/{id}
    /cars
    /cars/{id}
    /products
    /products/{id}
    /services
    /services/{id}

# /services


# /db
    x users(id, name, email, password, \user_type, ?profile_pic)
    x clients(id, name, phone, ?address, ?email, ?zip_code, ?tax_nr)
    x makes(id, name, ?logo)
    x models(id, name, \make, ?(... model info))
    x cars(id, plate, \model, ?chassi_nr, ?year, ?month, ?cc, ?engine_code, ?color_code)
    x schedules(id, date, description, ?\car, ?\model, ?\client)
    x products(id, designation, reference, \product_type, (... product info))
    x services(id, \client, ?[user_time], ?kms,  ?checkin_date, ?checkout_date,?malfunction_description, ?service_description, ?\car, ?[applied_products])
    
    Enums: 
        x user_type(id, designation)
        x product_type(id, designation)

    Arrays:
        x services_user_time(id,\service,\user, minutes, date) -- motivo do id: um user pode ter varios user_times no mesmo servico
        x service_applied_products(id, \service, \product, ?quantity, is_applied)
        --services_applied_products(\services,\applied_products)
    

# /public


# Prompts about security:
Good — tokens are the right direction if you want this to behave like a real API used by external clients.

I’ll keep it practical and show you a clean PHP approach without frameworks.

🧠 The idea (simple)
User logs in with email/password
Server generates a token
Client stores token
Every request sends token
API validates token → identifies user → checks role
🔐 1. Login → generate token
POST /api/auth/login
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
Verify user
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindValue(':email', $email);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}
Generate token

Simple secure token:

$token = bin2hex(random_bytes(32));
Store token in DB

You need a table:

CREATE TABLE tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    token TEXT,
    expires_at INTEGER
);

Insert:

$stmt = $db->prepare("
    INSERT INTO tokens (user_id, token, expires_at)
    VALUES (:user_id, :token, :expires)
");

$stmt->bindValue(':user_id', $user['id']);
$stmt->bindValue(':token', $token);
$stmt->bindValue(':expires', time() + 3600); // 1 hour
$stmt->execute();
Return token
echo json_encode([
    "token" => $token
]);
📤 2. Client sends token

Every request:

GET /api/users
Authorization: Bearer abc123...
🔐 3. Validate token (middleware style)

Create function:

function authenticate(PDO $db): array
{
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    $stmt = $db->prepare("
        SELECT users.*
        FROM tokens
        JOIN users ON users.id = tokens.user_id
        WHERE tokens.token = :token
        AND tokens.expires_at > :now
    ");

    $stmt->bindValue(':token', $token);
    $stmt->bindValue(':now', time());
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        exit;
    }

    return $user;
}
🧱 4. Use it in API endpoints
$user = authenticate($db);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    exit;
}
🔥 5. Logout (optional but important)
DELETE FROM tokens WHERE token = :token
🧠 How this architecture works
Client
  ↓ (token login)
API login → returns token
  ↓
Client stores token
  ↓
Each request sends token
  ↓
API validates token
  ↓
Gets user + role
  ↓
Allows or denies action
⚖️ Why this is good for your case

✔ works with external apps
✔ works with mobile later
✔ stateless-ish (DB-backed tokens)
✔ simple to debug
✔ industry standard pattern

⚠️ Important security upgrades (later)

When you improve it:

add HTTPS (mandatory in production)
hash tokens in DB (optional advanced)
refresh tokens (for long sessions)
rate limiting
👍 Bottom line

This is the correct architecture for your goal:

login → issue token
client sends token
API validates token every request
roles checked after authentication

If you want next step, I can show you:
👉 a clean “auth middleware + router” structure so your API stops becoming repetitive and messy.


