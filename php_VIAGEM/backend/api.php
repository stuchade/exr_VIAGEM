<?php

header('Content-Type: application/json; charset=utf-8');

function send_json($data, int $status = 200): never {
	http_response_code($status);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

$action = $_GET['action'] ?? '';

$geojsonFiles = [
	'Jicin.geojson',
	'Kopidlno.geojson',
	'Liban.geojson',
	'Nova_Paka.geojson'
];

$combinedGeoJSON = [
	'type' => 'FeatureCollection',
	'features' => []
];


// Sjednocení souborů geoJSON
foreach ($geojsonFiles as $file) {
	if (file_exists($file)) {
		$geojsonContent = file_get_contents($file);
		$geojson = json_decode($geojsonContent, true);

		if (isset($geojson['features']) && is_array($geojson['features'])) {
			$combinedGeoJSON['features'] = array_merge($combinedGeoJSON['features'], $geojson['features']);
		}
	}
}

$features = $combinedGeoJSON['features'];

if ($action === 'parcels') {
	send_json($combinedGeoJSON);
}

// načtení features předem do parcelID, pro rychlejší hledání
$parcelID = [];
foreach ($combinedGeoJSON['features'] as $feature) {
	$id = $feature['properties']['gml_id'] ?? null;
	if ($id) {
		$parcelID[$id] = $feature;
	}
}

// načtení informací o pracele
if ($action === 'parcel') {
	$id = $_GET['id'] ?? null;

	if (isset($parcelID[$id])) {
		$feature = $parcelID[$id];
		$props = $feature['properties'] ?? [];

		$featureId = $props['gml_id'] ?? null;

		if ($featureId === $id) {
			$parcelDetail = [
				'id' => $featureId,
				'parcelNumber'=> $props['label'] ?? 'Neznámé',
				'area' => $props['areaValue'] ?? 0,
				'cadastralArea' => (str_starts_with($props['nationalCadastralReference'], '659541') ? 'Jičín (659541)' : 
									(str_starts_with($props['nationalCadastralReference'], '669296') ? 'Kopidlno (669296)' : 
									(str_starts_with($props['nationalCadastralReference'], '681679') ? 'Libáň (681679)' :
									(str_starts_with($props['nationalCadastralReference'], '705128') ? 'Nová Paka (705128)' : '-')))),
				'landType' => (str_starts_with($props['label'] ?? '', 'st.') ? 'zastavěná plocha / stavební parcela' : 'pozemková parcela'),
				'ownerId' => 'owner-001'

			];
			send_json($parcelDetail);
		}
	}

	send_json(['error' => 'Parcel not found'], 404);
}

// načtení informací o vlatníkovi
if ($action === 'owner') {
	$owners = [
		'owner-001' => [
			'id' => 'owner-001',
			'name' => 'Katastrální úřad / Ukázkový vlastník',
			'address' => 'Czech Republic'
		]
	];

	$id = $_GET['id'] ?? null;

	if (isset($owners[$id])) {
		send_json($owners[$id]);
	}

	send_json(['error' => 'Owner not found'], 404);
}

send_json(['error' => 'Unknown action'], 404);
