#!/usr/bin/env node
/**
 * One-off generator: covers Brunei with H3 resolution-7 cells (~5km edge length) using the same
 * district boundary GeoJSON already served to the GIS map (public/geojson/brunei-districts.json),
 * assigns each cell the district whose polygon it falls inside, and writes
 * database/data/brunei-cells.json for RiskCellSeeder to upsert.
 *
 * elevation_m and drainage_capacity are deliberately left null here — there is no verified
 * survey/GIS data source for either wired up yet, and guessing those numbers would be exactly
 * the kind of fabricated risk data this project has been explicitly avoiding elsewhere.
 * RiskScoreService's terrain factor already has documented neutral defaults for cells with no
 * elevation/capacity on file, so leaving them null does not break scoring.
 *
 * Run with: node scripts/generate-cells.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import * as h3 from 'h3-js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const RESOLUTION = 7;
const POINTS_PER_SYNC_REQUEST = 100; // matches WeatherService::MAX_POINTS_PER_REQUEST

const geojsonPath = path.join(__dirname, '..', 'public', 'geojson', 'brunei-districts.json');
const outPath = path.join(__dirname, '..', 'database', 'data', 'brunei-cells.json');

const geojson = JSON.parse(fs.readFileSync(geojsonPath, 'utf8'));

function districtNameFor(rawName) {
    // Matches the same NAME_1 -> display-name fix already applied client-side in gis-map.blade.php.
    return rawName === 'BruneiandMuara' ? 'Brunei-Muara' : rawName;
}

const cellsByIndex = new Map();

for (const feature of geojson.features) {
    const district = districtNameFor(feature.properties.NAME_1);
    const geometry = feature.geometry;

    // Normalize to a list of Polygons: each Polygon is [outerRing, ...holeRings], each ring a
    // list of [lng, lat] pairs (standard GeoJSON coordinate order).
    const polygons = geometry.type === 'MultiPolygon' ? geometry.coordinates : [geometry.coordinates];

    for (const polygon of polygons) {
        // isGeoJson=true tells h3-js the rings are in [lng, lat] order and to respect GeoJSON
        // winding rules, instead of its own default [lat, lng] convention.
        const cells = h3.polygonToCells(polygon, RESOLUTION, true);

        for (const cell of cells) {
            if (cellsByIndex.has(cell)) {
                continue;
            }

            const [lat, lng] = h3.cellToLatLng(cell);
            cellsByIndex.set(cell, {
                h3_index: cell,
                lat: Number(lat.toFixed(7)),
                lng: Number(lng.toFixed(7)),
                district,
                elevation_m: null,
                drainage_capacity: null,
            });
        }
    }
}

const cells = Array.from(cellsByIndex.values());

fs.mkdirSync(path.dirname(outPath), { recursive: true });
fs.writeFileSync(outPath, JSON.stringify(cells, null, 2) + '\n');

const requestsPerSync = Math.ceil(cells.length / POINTS_PER_SYNC_REQUEST);

console.log(`Generated ${cells.length} H3 resolution-${RESOLUTION} cells covering Brunei.`);
console.log(`Written to ${path.relative(process.cwd(), outPath)}`);
console.log(
    `risk:sync batches ${POINTS_PER_SYNC_REQUEST} points/request, so each hourly sync needs `
    + `${requestsPerSync} Open-Meteo forecast request(s).`
);
