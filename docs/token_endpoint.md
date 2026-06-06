# Endpoint: Generování přístupového tokenu

### Základní informace o endpointu

* **Název:** Token Generator API Endpoint
* **Endpoint:** `/api/token.php`
* **HTTP metoda:** `POST`
* **Verze API:** `1.0.0`
* **Formát odpovědi:** `application/json`
* **Autentizace:** není vyžadována (slouží k jejímu získání)
* **Účel:** Vrací dočasný přístupový token (Access Token) na základě klientských údajů (`client_id` a `client_secret`). Vrácený token se používá pro autorizaci k dalším zabezpečeným endpointům.
* **Použití:** Autentizace klienta pro přístup k API. Bez platného tokenu není možné volat ostatní API endpointy.

***

### URL a parametry požadavku

**URL (příklad):**
`https://vlasato23.sps-prosek.cz/weby/api/token.php`

**Parametry (Form Data nebo JSON tělo požadavku):**

<table><thead><tr><th>Parametr</th><th width="333">Popis</th><th>Povinné</th><th>Výchozí hodnota</th></tr></thead><tbody><tr><td><code>client_id</code></td><td>Veřejné UID vygenerované v administraci (API Keys)</td><td>Ano</td><td>null</td></tr><tr><td><code>client_secret</code></td><td>Tajný klíč vygenerovaný v administraci (API Keys)</td><td>Ano</td><td>null</td></tr></tbody></table>

#### Struktura JSON odpovědi:

```json
{
    "access_token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

#### Popis funkčnosti 

1. Endpoint přijímá výhradně metodu `POST`. Při použití jiné metody (např. GET) vrátí HTTP 405 Method Not Allowed.
2. Lze odeslat data buď jako `application/x-www-form-urlencoded` nebo jako `application/json`.
3. Validuje přítomnost parametrů `client_id` a `client_secret`. Pokud některý chybí, vrací 400 Bad Request.
4. Ověřuje přihlašovací údaje proti databázi (tabulka `api_keys`). Heslo (`client_secret`) je kontrolováno bezpečně pomocí hashe.
5. Pokud údaje nesouhlasí, vrací 401 Unauthorized.
6. Při úspěchu smazává ze systému případné expirované tokeny daného klienta pro údržbu databáze.
7. Vygeneruje bezpečný náhodný token s platností 1 hodina (3600 sekund).
8. Uloží vygenerovaný token do databáze a vrátí ho klientovi v JSON formátu.

#### **HTTP status kódy** 

* `200 OK` — Přihlašovací údaje jsou správné, token byl vygenerován a vrácen.
* `400 Bad Request` — Chybí povinný parametr `client_id` nebo `client_secret`.
* `401 Unauthorized` — Neplatné `client_id` nebo nesprávný `client_secret`.
* `405 Method Not Allowed` — Byla použita nesprávná HTTP metoda (požadováno POST).

#### Význam klíčových polí 

<table><thead><tr><th>Pole</th><th width="385">Význam</th></tr></thead><tbody><tr><td>access_token</td><td>Vygenerovaný token, který je nutné posílat v hlavičce <code>Authorization: Bearer &#x3C;token></code></td></tr><tr><td>token_type</td><td>Typ tokenu (vždy <code>Bearer</code>)</td></tr><tr><td>expires_in</td><td>Doba platnosti tokenu v sekundách (3600 = 1 hodina)</td></tr></tbody></table>

#### Příklady použití 

a) CURL — Odeslání pomocí Form Data

```bash
curl -X POST "https://vlasato23.sps-prosek.cz/weby/api/token.php" \
     -d "client_id=123456789" \
     -d "client_secret=muj_tajny_klic"
```

b) CURL — Odeslání pomocí JSON těla

```bash
curl -X POST "https://vlasato23.sps-prosek.cz/weby/api/token.php" \
     -H "Content-Type: application/json" \
     -d '{"client_id":"123456789", "client_secret":"muj_tajny_klic"}'
```

### Příklad chybových odpovědí

#### 1) Chybějící parametry (400)

```json
{
  "error": "Missing client_id or client_secret"
}
```

#### 2) Neplatné údaje (401)

```json
{
  "error": "Invalid credentials"
}
```

#### 3) Špatná HTTP metoda (405)

```json
{
  "error": "Method Not Allowed"
}
```
