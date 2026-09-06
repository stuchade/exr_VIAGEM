<?php

header('Content-Type: application/json; charset=utf-8');

function send_json($data, int $status = 200): never {
	http_response_code($status);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

$action = $_GET['action'] ?? '';

$geojsonPath = 'Jicin.geojson';

if (!file_exists($geojsonPath)) {
	send_json(['error'=> 'GeoJSON file not found'], 500);
}

$geojsonContent = file_get_contents($geojsonPath);
$geojson = json_decode($geojsonContent, true);

$features = $geojson['features'] ?? [];

if ($action === 'parcels') {
	send_json($geojson);
}


if ($action === 'parcel') {
	$id = $_GET['id'] ?? null;

	foreach ($features as $feature) {
		$props = $feature['properties'] ?? [];

		$featureId = $props['gml_id'] ?? null;

		if ($featureId === $id || $props['gml_id'] === $id) {
			$parcelDetail = [
				'id' => $featureId,
				'parcelNumber'=> $props['label'] ?? 'Neznámé',
				'area' => $props['areaValue'] ?? 0,
				'cadastralArea' => 'Jičín (k.ú. kód: '. ($props['nationalCadastralReference'] ?? 'neuvedeno') . ')',
				'landType' => (str_starts_with($props['label'] ?? '', 'st.') ? 'zastavěná plocha / stavební parcela' : 'pozemková parcela'),
				'ownerId' => 'owner-001'

			];
			send_json($parcelDetail);
		}
	}

	send_json(['error' => 'Parcel not found'], 404);
}


if ($action === 'owner') {
	$owners = [
		'owner-001' => [
			'id' => 'owner-001',
			'name' => 'Katastrální úřad / Ukázkový vlastník',
			'address' => 'Jičín, Czech Republic'
		]
	];

	$id = $_GET['id'] ?? null;

	if (isset($owners[$id])) {
		send_json($owners[$id]);
	}

	send_json(['error' => 'Owner not found'], 404);
}


send_json(['error' => 'Unknown action'], 404);
