<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

if (!isset($_GET['doi'])) {
    exit('<form><label>DOI<input name="doi"></label><button type="submit">Fetch XML</button></form>');
}

$client = new Client([
    //'debug' => true
]);

$response = $client->get('eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', [
    'query' => [
        'db' => 'pmc',
        'term' => sprintf('"%s"[DOI]', $_GET['doi']),
        'retmode' => 'json',
        'retmax' => 1,
    ]
]);

$data = json_decode($response->getBody());

$result = $data->esearchresult;

if (!(int) $result->count) {
    http_response_code(404);
    exit();
}

header('Content-Type: application/xml');

$client->get('eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi', [
    'query' => [
        'db' => 'pmc',
        'id' => implode(',', $result->idlist),
    ],
    'sink' => fopen('php://output', 'w')
]);


