<?php
include_once __DIR__ . '/../library/api_helpers.php';
include_once __DIR__ . '/../library/logging.php';

$base_api_url = build_base_api_url('itemList.php');
api_log_info('Opened itemListOverview page');
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housing Data API – Výpis záznamů</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #2c3e50; }
        .form-container { background: #f8f9fa; padding: 24px; border-radius: 8px; border: 1px solid #dee2e6; }
        label { display: block; margin: 12px 0 6px; font-weight: 500; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 1rem; }
        button { margin-top: 20px; padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; }
        button:hover { background: #0056b3; }
        .info { margin-top: 30px; color: #555; font-size: 0.95rem; }
        pre { background: #f1f3f5; padding: 12px; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>Housing Data API</h1>
<p>Vyberte, kolik záznamů chcete na stránku a kterou stránku chcete zobrazit.</p>

<div class="form-container">
    <form id="paginationForm" action="<?= htmlspecialchars($base_api_url) ?>" method="get" target="_blank">
        
        <label for="per_page">Počet položek na stránku:</label>
        <select name="per_page" id="per_page">
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>

        <label for="page">Číslo stránky:</label>
        <input type="number" name="page" id="page" min="1" value="1" required>

        <button type="submit">Zobrazit data</button>
    </form>
</div>

<div class="info">
    <p><strong>Příklady přímých odkazů:</strong></p>
    <ul>
        <li><a href="<?= htmlspecialchars($base_api_url) ?>?page=1&per_page=10" target="_blank">První stránka, 10 záznamů</a></li>
        <li><a href="<?= htmlspecialchars($base_api_url) ?>?page=3&per_page=25" target="_blank">3. stránka, po 25</a></li>
        <li><a href="<?= htmlspecialchars($base_api_url) ?>?per_page=50" target="_blank">První stránka, po 50</a></li>
    </ul>

    <p>Výsledek bude vždy ve formátu JSON.</p>
</div>

<script>
document.getElementById('paginationForm').addEventListener('input', function() {
    const form = this;
    const params = new URLSearchParams(new FormData(form)).toString();
    console.log('Aktuální URL:', '<?= htmlspecialchars($base_api_url) ?>?' + params);
});
</script>

</body>
</html>