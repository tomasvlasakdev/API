# London Housing Data v1

Podívejte se na novinky a vylepšení v naší poslední aktualizaci.

---

## 🚀 Nové funkce

### Funkce #1: Dashboard s přehledem dat

**Popis funkce:**
- Interaktivní přístrojová deska pro sledování klíčových metrik
- Zobrazení počtu záznamů v databázi
- Statistiky cen nemovitostí (medián a průměr v £)
- Informace o počtu prodaných nemovitostí
- Sledování velikosti databáze
- Časové razítko poslední importace dat

---

### Funkce #2: Autentizace a správa uživatelů

**Popis funkce:**
- Přihlašování přes Google OAuth 2.0
- Role-based access control (RBAC) se třemi úrovněmi (admin, developer, visitor)
- Správa uživatelů jen pro administrátory
- Možnost blokování/odblokování uživatelů
- Mazání uživatelů z databáze
- Změna rolí uživatelů
- Zcela zabezpečená autentizace přes session management

---

### Funkce #3: Push notifikace

**Popis funkce:**
- Integrace OneSignal pro push notifikace
- Spravování odběru (subscribe/unsubscribe)
- Ukládání Push Subscription ID v databázi
- Sledování stavu notifikací
- API endpoint pro odesílání notifikací

---

### Funkce #4: Import a synchronizace dat

**Popis funkce:**
- Automatický download datových souborů
- Import CSV datových sad o londýnských nemovitostech (MSOA)
- Dávkové vkládání 5000 řádků (optimalizace výkonu)
- Validace řádků a parsování datových formátů
- Automatické čištění tabulky přesímportem
- Detailní logování procesu importu

---

### Funkce #5: Pokročilé logování

**Popis funkce:**
- Strukturované logování do JSON formátu
- Různé úrovně logů (INFO, ERROR, IMPORT, DOWNLOAD)
- Sledování zdrojových souborů a řádků
- Unikátní request ID a process ID (PID)
- Filtrování logů podle úrovní
- Řazení logů (nejnovější/nejstarší)
- Paginace pro velké objemy logů

---

### Funkce #6: SQL příkazy a datové dotazy

**Popis funkce:**
- Předdefinované SQL příkazy pro běžné analýzy
- Dotazy na statistiky cen nemovitostí
- Počet transakcí a prodejů
- Velikost a stav databáze
- Optimalizované výkony a prepared statements
- Ochrana proti SQL injection útokům

---

## ✨ Vylepšení

### Vylepšení #1: Paginace s intuitivním UI

**Popis:**
- Moderní paginační komponenta s JS
- Navigace mezi stránkami
- Zvýraznění aktivní stránky
- Podpora "Předchozí" a "Další" tlačítek
- Omezené zobrazení čísel stránek
- Responsive design

---

### Vylepšení #2: Sidebar navigace

**Popis:**
- Jednotná navigace across všech stránek
- Aktivní zvýraznění aktuální stránky
- Kontextová změna menu (přihlášení/odhlášení)
- Responsive design pro mobilní zařízení
- Integrovaná metadata aplikace

---

### Vylepšení #3: Formátování a čitelnost logů

**Popis:**
- Barevné zvýraznění úrovní logů (HTML výstup)
- Automatické formátování časových razítek
- Čitelný text formát i HTML formát
- Bezpečné escapování speciálních znaků
- Jasné označení zdroje (soubor a funkce)

---

### Vylepšení #4: REST API endpoint /info

**Popis:**
- Kontrola stavu API a databáze
- Informace o verzi API (v1.0.0)
- Detaily o serveru (PHP verze, software)
- Monitoring dostupnosti služby
- JSON výstup s strukturovanými daty
- Automatické testování databázového připojení

---

### Vylepšení #5: Bezpečnost a validace

**Popis:**
- Ověření přihlášení na chráněných stránkách
- Filtrování a validace vstupů
- Prepared SQL statements
- Role-based authorization kontroly
- CORS a session management
- Kontrola oprávnění (requireLogin, requireRole)

---

## 📊 Technické detaily

### Architektura
- **Framework:** Čistý PHP bez frameworku
- **Databáze:** MySQL/MariaDB s PDO
- **Autentizace:** Google OAuth 2.0
- **Push notifikace:** OneSignal
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **API:** RESTful endpoints

### Minimální požadavky
- PHP 7.4+
- MySQL 5.7+
- OpenSSL pro HTTPS
- Composer pro dependency management

### Hlavní komponenty
- `public/` - Frontend UI a veřejné stránky
- `library/` - Sdílené funkce a SQL příkazy
- `src/` - Backend logika (import, download, logging)
- `endpoints/` - API endpointy (/info, atd.)
- `config/` - Konfigurační soubory a datová schémata
- `logs/` - Logování a monitoring

---

## 🔄 Workflow

1. **Inicializace:** Uživatel se přihlásí přes Google OAuth
2. **Dashboard:** Zobrazení přehledu dat a statistik
3. **Data Import:** Automatický download a import londýnských dat o nemovitostech
4. **Monitorování:** Sledování logů a stavu služby
5. **Správa:** Admini mohou spravovat uživatele a notifikace

---

## 📝 Poznámky

- Veškeré citlivé údaje jsou uloženy v `config.php` (nikoli ve verzování)
- Logování je klíčové pro debugging - viz `/logs/logging.json`
- API dokumentace: https://app.gitbook.com/o/CJz4qlCVwDL2Hn3AuhmU/s/r2ekOEUU8ZTbgSwKjutn/
- Systém je optimalizován pro velké datové sady (miliony záznamů)

---

**Verze:** 1.0.0  
**Poslední aktualizace:** Leden 2026  
**Status:** Operační ✅
