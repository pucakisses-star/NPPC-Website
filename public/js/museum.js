/* ============================================================================
   NPPC Museum of Political Prisoner History
   A first-person walkable gallery built with Three.js (vendored, r160).

   Rooms: entrance rotunda (reflective marble, bronze "broken chain," title
   wall, projection band, standees) → timeline hall (mural + faces corridor)
   → six themed galleries → archive room (vitrines) → theater (slideshow /
   mp4) → replica solitary cell.

   Realism approach: ACES tone mapping, PBR materials from ambientCG texture
   sets, an IBL room environment, a constant-size dynamic light pool that is
   repositioned per room (no shader recompiles), emissive picture-light
   fixtures with additive wall/floor washes per artwork, glass glare planes,
   and a real-time planar reflection under the rotunda marble.

   Content arrives via window.MUSEUM (see SiteController@museum).
   ========================================================================== */

import * as THREE from 'three';
import { Reflector } from 'three/addons/objects/Reflector.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

const DATA = Object.assign(
    { galleries: [], faces: [], standees: [], timeline: [], archive: [], slides: [], stats: {}, video: null },
    window.MUSEUM || {}
);

/* Downscaled variants served by /thumb/{w}/... (SiteController@imageThumb);
   anything not on the public disk passes through untouched. The server falls
   back to a redirect to the original if it can't resize, so this is safe. */
function thumbUrl(url, w) {
    if (!url) return url;
    // photo URLs arrive APP_URL-absolute (Storage::url), so match anywhere and
    // emit a relative same-origin /thumb/ URL
    const i = url.indexOf('/storage/');
    return i === -1 ? url : '/thumb/' + w + '/' + url.slice(i + 9);
}
/* Mosaic atlases batch their canvas repaints: cells draw as thumbs arrive, and
   the (expensive) GPU re-upload happens at most once per pump interval. */
const atlasDirty = new Set();
function flushAtlases() {
    atlasDirty.forEach((t) => { t.needsUpdate = true; });
    atlasDirty.clear();
}

/* ---------------------------------------------------------------- palette */
const INK = '#1e2122';
const PAPER = '#f4f1ea';
const GOLD = '#e4a524';
const CRIMSON = '#98002e';
const TEAL = '#2a6d81';
const ACCENTS = [GOLD, CRIMSON, TEAL, '#7c5cbf', '#b0592e', '#4e7d3a'];

/* ------------------------------------------------------------------ boot */
const canvas = document.getElementById('museum-canvas');
// Low-end heuristic: weaker devices skip MSAA and start at a lower resolution.
const LOW_END = (navigator.hardwareConcurrency || 8) <= 4 || (navigator.deviceMemory || 8) <= 4;
const renderer = new THREE.WebGLRenderer({ canvas, antialias: !LOW_END, powerPreference: 'high-performance' });
// Adaptive resolution: the pixel ratio floats between a floor and a cap based
// on measured frame time (see tick), so the scene stays smooth on any GPU.
const DPR_CAP = Math.min(window.devicePixelRatio || 1, LOW_END ? 1.0 : 1.5);
const DPR_FLOOR = 0.66;
let curDPR = DPR_CAP;
renderer.setPixelRatio(curDPR);
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.shadowMap.autoUpdate = false;          // re-rendered on room change / periodically
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.12;
renderer.outputColorSpace = THREE.SRGBColorSpace;

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x0c0d10);

const camera = new THREE.PerspectiveCamera(72, window.innerWidth / window.innerHeight, 0.05, 120);

const pmrem = new THREE.PMREMGenerator(renderer);
scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

/* ------------------------------------------------------------- texture lib */
THREE.Cache.enabled = true;          // collapse duplicate in-flight image fetches
const texLoader = new THREE.TextureLoader();
// Anisotropy claims real GPU bandwidth — clamp to the hardware max and use a
// lower tier for small props (placards, mosaic tiles) that never fill the view.
const ANISO = Math.min(8, renderer.capabilities.getMaxAnisotropy() || 8);
const ANISO_LOW = Math.min(2, ANISO);
/* The 21 PBR files are referenced from ~33 pbr() call sites; memoize so each
   file is fetched and uploaded to the GPU once — clones share the .source, so
   per-use wrap/repeat stays free. */
const pbrMemo = new Map();
function pbr(base, { repeat = [1, 1], color = true, normal = true, rough = true, ao = false, metal = false } = {}) {
    const out = {};
    const load = (suffix, isColor) => {
        const key = base + '_' + suffix;
        let entry = pbrMemo.get(key);
        if (!entry) {
            entry = { clones: [] };
            entry.master = texLoader.load(`/images/museum/textures/${key}.jpg`,
                // a clone made before the image arrived stays at version 0 and
                // would never reach the GPU — bump every clone on arrival
                () => entry.clones.forEach((c) => { c.needsUpdate = true; }));
            entry.master.wrapS = entry.master.wrapT = THREE.RepeatWrapping;
            entry.master.anisotropy = ANISO;
            if (isColor) entry.master.colorSpace = THREE.SRGBColorSpace;
            pbrMemo.set(key, entry);
        }
        const t = entry.master.clone();           // shares .source → one GPU upload
        t.repeat.set(repeat[0], repeat[1]);       // repeat set on the clone only
        if (entry.master.image) t.needsUpdate = true;
        else entry.clones.push(t);
        return t;
    };
    if (color) out.map = load('col', true);
    if (normal) out.normalMap = load('nrm', false);
    if (rough) out.roughnessMap = load('rgh', false);
    if (ao) out.aoMap = load('ao', false);
    if (metal) out.metalnessMap = load('met', false);
    return out;
}

const MAT = {
    galleryFloor: new THREE.MeshStandardMaterial({ ...pbr('woodfloor', { repeat: [6, 6], ao: true }), roughness: 1, envMapIntensity: 0.7 }),
    hallFloor: new THREE.MeshStandardMaterial({ ...pbr('woodfloor', { repeat: [3, 10], ao: true }), roughness: 1, envMapIntensity: 0.7 }),
    wall: new THREE.MeshStandardMaterial({ ...pbr('plaster', { repeat: [3, 1.6] }), color: 0xe8e4da, envMapIntensity: 0.28 }),
    wallDark: new THREE.MeshStandardMaterial({ ...pbr('plaster', { repeat: [3, 1.6] }), color: 0x3c4046, envMapIntensity: 0.22 }),
    ceiling: new THREE.MeshStandardMaterial({ ...pbr('plaster', { repeat: [4, 4] }), color: 0xdcd8cf, envMapIntensity: 0.15 }),
    marble: new THREE.MeshStandardMaterial({ ...pbr('marble', { repeat: [5, 5] }), envMapIntensity: 1.0, roughness: 0.9 }),
    plinth: new THREE.MeshStandardMaterial({ ...pbr('marble', { repeat: [1.2, 1.2] }), envMapIntensity: 0.9 }),
    concrete: new THREE.MeshStandardMaterial({ ...pbr('concrete', { repeat: [2, 1.4] }), color: 0x6f7478, envMapIntensity: 0.12 }),
    concreteFloor: new THREE.MeshStandardMaterial({ ...pbr('concrete', { repeat: [2, 2] }), color: 0x5f6468, envMapIntensity: 0.1 }),
    metal: new THREE.MeshStandardMaterial({ ...pbr('metal', { repeat: [1, 1], metal: true }), metalness: 1, envMapIntensity: 0.9 }),
    frameWood: new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [0.6, 0.6] }), color: 0x4a3524, roughness: 0.55, envMapIntensity: 0.8 }),
    frameBlack: new THREE.MeshStandardMaterial({ color: 0x17181b, roughness: 0.35, metalness: 0.15, envMapIntensity: 1.1 }),
    frameGilt: new THREE.MeshStandardMaterial({ color: 0xa8853c, roughness: 0.32, metalness: 0.85, envMapIntensity: 1.3 }),
    mat: new THREE.MeshStandardMaterial({ color: 0xf1ede2, roughness: 0.95, envMapIntensity: 0.25 }),
    benchWood: new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1.4, 0.5] }), envMapIntensity: 0.6 }),
    fabric: new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [1.6, 0.8] }), color: 0x5c1f31, envMapIntensity: 0.3 }),
    bronze: new THREE.MeshStandardMaterial({ color: 0x6d4f2a, metalness: 0.95, roughness: 0.38, envMapIntensity: 1.4 }),
    glass: new THREE.MeshPhysicalMaterial({ color: 0xffffff, transparent: true, opacity: 0.09, roughness: 0.05, metalness: 0, envMapIntensity: 2.2, depthWrite: false }),
    brass: new THREE.MeshStandardMaterial({ color: 0xb08d3e, metalness: 0.9, roughness: 0.3, envMapIntensity: 1.2 }),
};

/* --------------------------------------------------------- canvas textures */
function canvasTexture(w, h, draw) {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    draw(c.getContext('2d'), w, h);
    const t = new THREE.CanvasTexture(c);
    t.anisotropy = ANISO_LOW;
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
}
function wrapText(g, text, x, y, maxW, lh, maxLines = 99) {
    const words = String(text || '').split(/\s+/);
    let line = '', lines = 0;
    for (let i = 0; i < words.length; i++) {
        const test = line ? line + ' ' + words[i] : words[i];
        if (g.measureText(test).width > maxW && line) {
            g.fillText(line, x, y); y += lh; line = words[i];
            if (++lines >= maxLines - 1) { line += '…'; break; }
        } else line = test;
    }
    if (line) g.fillText(line, x, y);
    return y + lh;
}
function placardTexture(title, l1, l2) {
    return canvasTexture(512, 300, (g, w, h) => {
        g.fillStyle = PAPER; g.fillRect(0, 0, w, h);
        g.strokeStyle = 'rgba(0,0,0,.18)'; g.lineWidth = 3; g.strokeRect(8, 8, w - 16, h - 16);
        g.fillStyle = INK; g.font = '600 44px Georgia, serif'; g.textBaseline = 'top';
        wrapText(g, title, 34, 40, w - 68, 50, 2);
        g.fillStyle = CRIMSON; g.fillRect(34, 148, 64, 5);
        g.fillStyle = '#4c4a45'; g.font = '30px Georgia, serif';
        if (l1) g.fillText(String(l1).slice(0, 34), 34, 176);
        g.font = 'italic 27px Georgia, serif'; g.fillStyle = '#6a675f';
        if (l2) g.fillText(String(l2).slice(0, 40), 34, 220);
    });
}
function panelTexture(eyebrow, title, body) {
    return canvasTexture(1024, 1320, (g, w, h) => {
        g.fillStyle = '#23262b'; g.fillRect(0, 0, w, h);
        g.fillStyle = GOLD; g.fillRect(72, 96, 90, 8);
        g.font = '600 34px Georgia, serif'; g.fillStyle = '#b9b4a8'; g.textBaseline = 'top';
        g.fillText((eyebrow || 'GALLERY').toUpperCase(), 72, 130);
        g.fillStyle = '#f5f2ea'; g.font = '700 84px Georgia, serif';
        const yAfter = wrapText(g, title, 72, 196, w - 144, 92, 3);
        g.fillStyle = '#cfcabd'; g.font = '37px Georgia, serif';
        wrapText(g, body, 72, yAfter + 30, w - 144, 56, 14);
    });
}
function titleTexture() {
    const s = DATA.stats || {};
    return canvasTexture(2048, 820, (g, w, h) => {
        g.fillStyle = '#23262b'; g.fillRect(0, 0, w, h);
        g.strokeStyle = 'rgba(228,165,36,.55)'; g.lineWidth = 4; g.strokeRect(26, 26, w - 52, h - 52);
        g.textAlign = 'center'; g.textBaseline = 'top';
        g.fillStyle = GOLD; g.font = '600 44px Georgia, serif';
        g.fillText('N A T I O N A L   P O L I T I C A L   P R I S O N E R   C O A L I T I O N', w / 2, 68);
        g.fillStyle = '#f5f2ea'; g.font = '700 150px Georgia, serif';
        g.fillText('The Museum of', w / 2, 150);
        g.fillText('Political Imprisonment', w / 2, 315);
        g.fillStyle = '#c9c4b8'; g.font = 'italic 46px Georgia, serif';
        g.fillText('People jailed in America for what they believed, said, and organized', w / 2, 512);
        g.fillStyle = GOLD; g.fillRect(w / 2 - 90, 600, 180, 6);
        const stats = `${s.total || '—'} people documented   ·   ${s.inCustody || '—'} still in custody   ·   ${(DATA.timeline || []).length || '—'} moments on the timeline`;
        g.fillStyle = '#b9b4a8'; g.font = '40px Georgia, serif';
        g.fillText(stats, w / 2, 650);
    });
}
function washTexture() {
    return canvasTexture(256, 256, (g, w, h) => {
        const r = g.createRadialGradient(w / 2, h / 2, 10, w / 2, h / 2, w / 2);
        r.addColorStop(0, 'rgba(255,244,222,0.85)');
        r.addColorStop(0.55, 'rgba(255,240,214,0.28)');
        r.addColorStop(1, 'rgba(255,238,210,0)');
        g.fillStyle = r; g.fillRect(0, 0, w, h);
    });
}
const WASH_TEX = washTexture();

function timelineTexture(events) {
    const W = 8192, H = 720;
    const evs = events.slice(0, 30);
    return canvasTexture(W, H, (g, w, h) => {
        g.fillStyle = '#22252a'; g.fillRect(0, 0, w, h);
        g.fillStyle = GOLD; g.font = '600 42px Georgia, serif'; g.textBaseline = 'top';
        g.fillText('A  T I M E L I N E  O F  P O L I T I C A L  I M P R I S O N M E N T', 60, 44);
        if (!evs.length) return;
        const y0 = evs[0].y, y1 = evs[evs.length - 1].y || y0 + 1;
        const xFor = (yr) => 140 + (w - 280) * ((yr - y0) / Math.max(1, y1 - y0));
        g.strokeStyle = 'rgba(244,241,234,.5)'; g.lineWidth = 4;
        g.beginPath(); g.moveTo(100, h / 2); g.lineTo(w - 100, h / 2); g.stroke();
        evs.forEach((e, i) => {
            const x = xFor(e.y), up = i % 2 === 0, acc = ACCENTS[i % ACCENTS.length];
            g.fillStyle = acc;
            g.beginPath(); g.arc(x, h / 2, 13, 0, Math.PI * 2); g.fill();
            g.strokeStyle = 'rgba(244,241,234,.35)'; g.lineWidth = 2;
            g.beginPath(); g.moveTo(x, h / 2 + (up ? -13 : 13)); g.lineTo(x, h / 2 + (up ? -58 : 58)); g.stroke();
            g.textAlign = 'left';
            const bx = Math.min(Math.max(60, x - 120), w - 320);
            g.fillStyle = acc; g.font = '700 40px Georgia, serif';
            g.fillText(String(e.y), bx, up ? h / 2 - 168 : h / 2 + 72);
            g.fillStyle = '#e9e5da'; g.font = '31px Georgia, serif';
            wrapText(g, e.t, bx, up ? h / 2 - 118 : h / 2 + 122, 300, 37, 3);
        });
    });
}

/* ------------------------------------------------------------- world state */
const worldGroup = new THREE.Group();
scene.add(worldGroup);
const colliders = [];           // {minX,maxX,minZ,maxZ}
const floorZones = [];          // {minX,maxX,minZ,maxZ,y} — raised/lowered floors (cinema tiers)
const interactables = [];       // {mesh, data}
const artQueue = [];            // progressive photo loading
const rooms = [];               // {name,minX,maxX,minZ,maxZ,rig}
const slideshows = [];          // animated canvas screens

/* Colliders may carry a vertical band [y0,y1]; a walker only collides when
   their body (ground..ground+1.7) overlaps the band. Default = full height,
   so walls behave as before; balustrades on the mezzanine use a band so the
   ground floor can pass beneath them. */
function addCollider(minX, maxX, minZ, maxZ, y0 = -Infinity, y1 = Infinity) {
    colliders.push({ minX, maxX, minZ, maxZ, y0, y1 });
}
function addFloorZone(minX, maxX, minZ, maxZ, y) { floorZones.push({ minX, maxX, minZ, maxZ, y }); }
function floorHeightAt(x, z, cur = null) {
    // Level-aware: a zone only counts if it is climbable from the walker's
    // CURRENT height (a stair step, not a balcony 4.6m overhead). Without
    // this, walking beneath the mezzanine teleports you up into its slab.
    const from = cur === null ? player.ground : cur;
    let best = 0;
    for (const f of floorZones) {
        if (x >= f.minX && x <= f.maxX && z >= f.minZ && z <= f.maxZ) {
            if (f.y <= from + 0.55 && f.y > best) best = f.y;
        }
    }
    return best;
}
function box(w, h, d, mat, x, y, z, { shadow = true, collide = false, ry = 0 } = {}) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
    m.position.set(x, y, z); m.rotation.y = ry;
    m.castShadow = shadow; m.receiveShadow = true;
    worldGroup.add(m);
    if (collide) addCollider(x - w / 2 - 0.05, x + w / 2 + 0.05, z - d / 2 - 0.05, z + d / 2 + 0.05);
    return m;
}

/* Wall run along X or Z with door gaps. thickness .3, includes collision. */
const WALL_T = 0.3;
function wallRun(axis, fixed, from, to, height, { doors = [], mat = MAT.wall, base = 0 } = {}) {
    const gaps = doors.map(d => [d.at - d.w / 2, d.at + d.w / 2]).sort((a, b) => a[0] - b[0]);
    const spans = [];
    let cur = Math.min(from, to);
    const end = Math.max(from, to);
    for (const [g0, g1] of gaps) {
        if (g0 > cur) spans.push([cur, Math.min(g0, end)]);
        cur = Math.max(cur, g1);
    }
    if (cur < end) spans.push([cur, end]);
    // Colliders are 2D (x,z) and ignore height, so a raised filler band above a
    // doorway must NOT collide or it walls off the passage at floor level.
    const solid = base < 1.9;
    for (const [a, b] of spans) {
        if (b - a < 0.05) continue;
        const len = b - a, mid = (a + b) / 2;
        if (axis === 'x') box(len, height, WALL_T, mat, mid, base + height / 2, fixed, { collide: solid });
        else box(WALL_T, height, len, mat, fixed, base + height / 2, mid, { collide: solid });
    }
    // lintels over doors
    for (const d of doors) {
        const lh = height - (d.h || 2.4);
        if (lh > 0.05) {
            if (axis === 'x') box(d.w, lh, WALL_T, mat, d.at, base + (d.h || 2.4) + lh / 2, fixed, {});
            else box(WALL_T, lh, d.w, mat, fixed, base + (d.h || 2.4) + lh / 2, d.at, {});
        }
    }
}
function floorRect(minX, maxX, minZ, maxZ, mat, y = 0) {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(maxX - minX, maxZ - minZ), mat);
    m.rotation.x = -Math.PI / 2;
    m.position.set((minX + maxX) / 2, y, (minZ + maxZ) / 2);
    m.receiveShadow = true;
    if (m.geometry.attributes.uv) m.geometry.setAttribute('uv2', m.geometry.attributes.uv);
    worldGroup.add(m);
    return m;
}
function ceilRect(minX, maxX, minZ, maxZ, y, mat = MAT.ceiling) {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(maxX - minX, maxZ - minZ), mat);
    m.rotation.x = Math.PI / 2;
    m.position.set((minX + maxX) / 2, y, (minZ + maxZ) / 2);
    m.receiveShadow = true;
    worldGroup.add(m);
    return m;
}
function ceilingLight(x, y, z, w = 1.4, d = 0.22) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, 0.06, d),
        new THREE.MeshBasicMaterial({ color: 0xfff3dd }));
    m.position.set(x, y - 0.03, z);
    worldGroup.add(m);
    return m;
}

/* ------------------------------------------------------- artwork machinery */
const placeholderArt = canvasTexture(64, 80, (g, w, h) => {
    g.fillStyle = '#2c2e33'; g.fillRect(0, 0, w, h);
    const r = g.createRadialGradient(w / 2, h / 2, 4, w / 2, h / 2, w * 0.75);
    r.addColorStop(0, 'rgba(255,255,255,.10)'); r.addColorStop(1, 'rgba(0,0,0,0)');
    g.fillStyle = r; g.fillRect(0, 0, w, h);
});

/* Hang a framed work on a wall.
   pos = world position of art center; normal = outward wall normal. */
function hangArt(item, pos, normal, {
    artH = 1.15, frame = MAT.frameWood, placard = true, wash = true, light = true, gallery = '', edge = null
} = {}) {
    const g = new THREE.Group();
    g.position.copy(pos);
    g.lookAt(pos.clone().add(normal));
    worldGroup.add(g);

    const artW = artH * 0.8;                       // frame is fixed; photo letterboxes in
    const frameT = 0.07, frameD = 0.09, matPad = 0.09;

    // frame bars
    const ow = artW + matPad * 2 + frameT * 2, oh = artH + matPad * 2 + frameT * 2;
    // edge-lit glow behind the frame (accent halo, like the reference galleries)
    if (edge != null) {
        const halo = new THREE.Mesh(new THREE.PlaneGeometry(ow + 0.16, oh + 0.16),
            new THREE.MeshBasicMaterial({ color: edge, transparent: true, opacity: 0.28, blending: THREE.AdditiveBlending, depthWrite: false }));
        halo.position.z = 0.004; g.add(halo);
    }
    const bar = (bw, bh, bx, by) => {
        const m = new THREE.Mesh(new THREE.BoxGeometry(bw, bh, frameD), frame);
        m.position.set(bx, by, frameD / 2); m.castShadow = true; m.receiveShadow = true; g.add(m);
    };
    bar(ow, frameT, 0, oh / 2 - frameT / 2);
    bar(ow, frameT, 0, -oh / 2 + frameT / 2);
    bar(frameT, oh - frameT * 2, -ow / 2 + frameT / 2, 0);
    bar(frameT, oh - frameT * 2, ow / 2 - frameT / 2, 0);
    // mat board
    const matB = new THREE.Mesh(new THREE.BoxGeometry(artW + matPad * 2, artH + matPad * 2, 0.02), MAT.mat);
    matB.position.z = 0.028; matB.receiveShadow = true; g.add(matB);
    // photo
    const photoMat = new THREE.MeshStandardMaterial({
        map: placeholderArt, roughness: 0.85, envMapIntensity: 0.25,
        emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.34,
    });
    const photo = new THREE.Mesh(new THREE.PlaneGeometry(artW, artH), photoMat);
    photo.position.z = 0.045; g.add(photo);
    // glass with envmap glare
    const glass = new THREE.Mesh(new THREE.PlaneGeometry(artW + matPad * 2, artH + matPad * 2), MAT.glass);
    glass.position.z = 0.055; g.add(glass);

    if (item.img) {
        artQueue.push({
            url: item.img, pos: pos.clone(), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
                const a = tex.image.width / tex.image.height, target = artW / artH;
                photoMat.map = tex; photoMat.emissiveMap = tex; photoMat.needsUpdate = true;
                if (a > target) photo.scale.set(1, target / a, 1);
                else photo.scale.set(a / target, 1, 1);
            },
            // evictable: far-away gallery art can release its VRAM and reload
            // from the HTTP cache when the player returns
            reset: () => {
                photoMat.map = placeholderArt; photoMat.emissiveMap = placeholderArt;
                photoMat.needsUpdate = true;
            },
        });
    }
    if (wash) {
        const wm = new THREE.Mesh(new THREE.PlaneGeometry(oh * 2.1, oh * 2.1),
            new THREE.MeshBasicMaterial({ map: WASH_TEX, transparent: true, opacity: 0.4, blending: THREE.AdditiveBlending, depthWrite: false }));
        wm.position.z = 0.012; wm.position.y = -0.15; g.add(wm);
        const fp = new THREE.Mesh(new THREE.PlaneGeometry(oh * 1.7, oh * 1.15),
            new THREE.MeshBasicMaterial({ map: WASH_TEX, transparent: true, opacity: 0.16, blending: THREE.AdditiveBlending, depthWrite: false }));
        fp.rotation.x = -Math.PI / 2;
        fp.position.set(pos.x + normal.x * 0.7, 0.012, pos.z + normal.z * 0.7);
        worldGroup.add(fp);
    }
    if (light) {                                     // brass picture light
        const arm = new THREE.Mesh(new THREE.CylinderGeometry(0.016, 0.016, 0.32), MAT.brass);
        arm.rotation.x = Math.PI / 2.6;
        arm.position.set(0, oh / 2 + 0.16, 0.12); g.add(arm);
        const tube = new THREE.Mesh(new THREE.CylinderGeometry(0.032, 0.032, artW * 0.7, 12), MAT.brass);
        tube.rotation.z = Math.PI / 2;
        tube.position.set(0, oh / 2 + 0.24, 0.24); g.add(tube);
        const glowM = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, artW * 0.66, 8),
            new THREE.MeshBasicMaterial({ color: 0xffe9c4 }));
        glowM.rotation.z = Math.PI / 2;
        glowM.position.set(0, oh / 2 + 0.225, 0.255); g.add(glowM);
    }
    if (placard && (item.n || item.l1)) {
        const pm = new THREE.Mesh(new THREE.PlaneGeometry(0.46, 0.27),
            new THREE.MeshStandardMaterial({ map: placardTexture(item.n, item.l1, item.l2), roughness: 0.9, emissive: 0xffffff, emissiveIntensity: 0.06, emissiveMap: null }));
        pm.position.set(ow / 2 + 0.4, -0.25, 0.02);
        g.add(pm);
    }
    interactables.push({ mesh: photo, data: { kind: 'art', gallery, ...item } });
    interactables.push({ mesh: glass, data: { kind: 'art', gallery, ...item } });
    return g;
}

function wallPanel(tex, pos, normal, w, h, { emissive = 0.24, interact = null } = {}) {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h),
        new THREE.MeshStandardMaterial({ map: tex, roughness: 0.9, envMapIntensity: 0.12, emissive: 0xffffff, emissiveMap: tex, emissiveIntensity: emissive }));
    m.position.copy(pos);
    m.lookAt(pos.clone().add(normal));
    m.receiveShadow = true;
    worldGroup.add(m);
    if (interact) interactables.push({ mesh: m, data: interact });
    return m;
}

/* Standee: photo figure board on a floor stand. */
function standee(item, x, z, ry, band = null) {
    const g = new THREE.Group();
    g.position.set(x, 0, z); g.rotation.y = ry;
    worldGroup.add(g);
    const H = 1.78, W = H * 0.62;
    const boardMat = new THREE.MeshStandardMaterial({
        map: placeholderArt, roughness: 0.7, envMapIntensity: 0.3,
        emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.3,
    });
    const backer = new THREE.Mesh(new THREE.BoxGeometry(W + 0.06, H + 0.06, 0.03), new THREE.MeshStandardMaterial({ color: 0xf4f1ea, roughness: 0.9 }));
    backer.position.set(0, H / 2 + 0.06, 0); backer.rotation.x = -0.06; backer.castShadow = true; g.add(backer);
    const photo = new THREE.Mesh(new THREE.PlaneGeometry(W, H), boardMat);
    photo.position.set(0, H / 2 + 0.06, 0.017); photo.rotation.x = -0.06; g.add(photo);
    const brace = new THREE.Mesh(new THREE.BoxGeometry(0.05, H * 0.62, 0.04), MAT.frameBlack);
    brace.position.set(0, H * 0.33, -0.17); brace.rotation.x = 0.32; g.add(brace);
    const base = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.46, 0.05, 24), MAT.frameBlack);
    base.position.y = 0.025; base.receiveShadow = true; g.add(base);
    if (item.img) {
        artQueue.push({
            url: item.img, pos: new THREE.Vector3(x, 1.4, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
                boardMat.map = tex; boardMat.emissiveMap = tex; boardMat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = W / H;
                if (a > target) photo.scale.set(1, target / a, 1); else photo.scale.set(a / target, 1, 1);
            }
        });
    }
    addCollider(x - 0.45, x + 0.45, z - 0.3, z + 0.3, band ? band[0] : -Infinity, band ? band[1] : Infinity);
    interactables.push({ mesh: photo, data: { kind: 'standee', ...item } });
    const plStand = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.78), MAT.frameBlack);
    plStand.position.set(W / 2 + 0.28, 0.39, 0.12); g.add(plStand);
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.4, 0.235),
        new THREE.MeshStandardMaterial({ map: placardTexture(item.n, item.l1, item.l2), roughness: 0.9 }));
    pl.position.set(W / 2 + 0.28, 0.82, 0.13); pl.rotation.x = -0.35; g.add(pl);
    return g;
}

/* Vitrine: table + glass case + document propped inside. */
function vitrine(item, x, z, ry = 0, band = null) {
    const g = new THREE.Group();
    g.position.set(x, 0, z); g.rotation.y = ry;
    worldGroup.add(g);
    const tw = 1.15, td = 0.8, th = 0.92;
    const top = new THREE.Mesh(new THREE.BoxGeometry(tw, 0.06, td), MAT.benchWood);
    top.position.y = th - 0.03; top.castShadow = true; top.receiveShadow = true; g.add(top);
    const skirt = new THREE.Mesh(new THREE.BoxGeometry(tw - 0.08, th - 0.2, td - 0.08), new THREE.MeshStandardMaterial({ color: 0x2c2620, roughness: 0.7 }));
    skirt.position.y = (th - 0.2) / 2; skirt.castShadow = true; g.add(skirt);
    // glass case: five thin panes + black edge posts (edges sell the glass)
    const caseH = 0.52, gw = tw - 0.08, gd = td - 0.08;
    const paneMat = new THREE.MeshPhysicalMaterial({
        color: 0xf4faff, transparent: true, opacity: 0.055, roughness: 0.04,
        metalness: 0, envMapIntensity: 1.1, depthWrite: false, side: THREE.DoubleSide,
    });
    const pane = (pw, ph, x2, y2, z2, ry2 = 0, rx2 = 0) => {
        const p = new THREE.Mesh(new THREE.PlaneGeometry(pw, ph), paneMat);
        p.position.set(x2, y2, z2); p.rotation.y = ry2; p.rotation.x = rx2; g.add(p);
        return p;
    };
    const gy = th + caseH / 2;
    pane(gw, caseH, 0, gy, gd / 2);
    pane(gw, caseH, 0, gy, -gd / 2, Math.PI);
    pane(gd, caseH, -gw / 2, gy, 0, Math.PI / 2);
    pane(gd, caseH, gw / 2, gy, 0, -Math.PI / 2);
    for (const [px, pz] of [[-gw / 2, -gd / 2], [gw / 2, -gd / 2], [-gw / 2, gd / 2], [gw / 2, gd / 2]]) {
        const post = new THREE.Mesh(new THREE.BoxGeometry(0.022, caseH, 0.022), MAT.frameBlack);
        post.position.set(px, gy, pz); g.add(post);
    }
    // top rim as an open frame of four bars (a solid plate reads as a black lid)
    const rimY = th + caseH + 0.011, rimT = 0.035;
    for (const [bw, bd, bx, bz] of [
        [gw + 0.05, rimT, 0, gd / 2], [gw + 0.05, rimT, 0, -gd / 2],
        [rimT, gd + 0.05, -gw / 2, 0], [rimT, gd + 0.05, gw / 2, 0],
    ]) {
        const bar2 = new THREE.Mesh(new THREE.BoxGeometry(bw, 0.022, bd), MAT.frameBlack);
        bar2.position.set(bx, rimY, bz); g.add(bar2);
    }
    const glass = pane(gw, caseH, 0, gy, gd / 2 + 0.001);   // raycast face for inspect
    // document on a wedge stand
    const docMat = new THREE.MeshStandardMaterial({
        map: placeholderArt, roughness: 0.85,
        emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.38,
    });
    const doc = new THREE.Mesh(new THREE.PlaneGeometry(0.52, 0.68), docMat);
    doc.position.set(0, th + 0.3, -0.06);
    doc.rotation.x = -0.42;
    g.add(doc);
    if (item.img) {
        artQueue.push({
            url: item.img, pos: new THREE.Vector3(x, 1.1, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
                docMat.map = tex; docMat.emissiveMap = tex; docMat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = 0.52 / 0.68;
                if (a > target) doc.scale.set(1, target / a, 1); else doc.scale.set(a / target, 1, 1);
            }
        });
    }
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.42, 0.245),
        new THREE.MeshStandardMaterial({ map: placardTexture(item.n, item.l1, item.l2), roughness: 0.9 }));
    pl.position.set(0, th + 0.02, td / 2 - 0.1); pl.rotation.x = -Math.PI / 2 + 0.35; g.add(pl);
    addCollider(x - tw / 2 - 0.1, x + tw / 2 + 0.1, z - td / 2 - 0.1, z + td / 2 + 0.1, band ? band[0] : -Infinity, band ? band[1] : Infinity);
    interactables.push({ mesh: doc, data: { kind: 'doc', ...item } });
    interactables.push({ mesh: glass, data: { kind: 'doc', ...item } });
    return g;
}

function bench(x, z, ry = 0, fabricTop = false, band = null) {
    const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g);
    const seat = new THREE.Mesh(new THREE.BoxGeometry(1.7, 0.09, 0.48), fabricTop ? MAT.fabric : MAT.benchWood);
    seat.position.y = 0.46; seat.castShadow = true; seat.receiveShadow = true; g.add(seat);
    for (const sx of [-0.7, 0.7]) {
        const leg = new THREE.Mesh(new THREE.BoxGeometry(0.09, 0.42, 0.4), MAT.frameBlack);
        leg.position.set(sx, 0.21, 0); leg.castShadow = true; g.add(leg);
    }
    const c = Math.cos(ry), s = Math.sin(ry);
    const hw = 0.9 * Math.abs(c) + 0.3 * Math.abs(s), hd = 0.9 * Math.abs(s) + 0.3 * Math.abs(c);
    addCollider(x - hw, x + hw, z - hd, z + hd, band ? band[0] : -Infinity, band ? band[1] : Infinity);
    return g;
}

function plinth(x, z, h = 1.0, r = 0.34) {
    const m = new THREE.Mesh(new THREE.CylinderGeometry(r, r * 1.08, h, 28), MAT.plinth);
    m.position.set(x, h / 2, z);
    m.castShadow = true; m.receiveShadow = true;
    worldGroup.add(m);
    addCollider(x - r - 0.08, x + r + 0.08, z - r - 0.08, z + r + 0.08);
    return m;
}

/* Broken-chain bronze sculpture. */
function brokenChain(x, z) {
    plinth(x, z, 1.05, 0.5);
    const g = new THREE.Group();
    g.position.set(x, 1.05, z);
    worldGroup.add(g);
    const link = (yy, ry, arc = Math.PI * 2, off = 0) => {
        const m = new THREE.Mesh(new THREE.TorusGeometry(0.155, 0.045, 14, 40, arc), MAT.bronze);
        m.position.y = yy; m.rotation.y = ry; m.rotation.z = off;
        m.castShadow = true; g.add(m);
        return m;
    };
    link(0.16, 0);
    link(0.42, Math.PI / 2);
    link(0.68, 0);
    link(0.94, Math.PI / 2);
    const b1 = link(1.24, 0, Math.PI * 1.15, 0.4);
    b1.position.x = -0.1;
    const b2 = link(1.42, 0, Math.PI * 0.7, -2.2);
    b2.position.x = 0.14; b2.position.y = 1.38;
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.5, 0.29),
        new THREE.MeshStandardMaterial({ map: placardTexture('Unbroken', 'Bronze — the price of dissent', 'For every name in this building'), roughness: 0.9 }));
    pl.position.set(x, 1.13, z + 0.52); pl.rotation.x = -0.5;
    worldGroup.add(pl);
    interactables.push({
        mesh: g.children[0], data: {
            kind: 'panel', n: 'Unbroken', l1: 'Bronze', l2: '',
            d: 'A chain of five links, the topmost torn open. Every person remembered in this museum was meant to be a closed link — silenced, contained, forgotten. The break is the point: movements outlive prisons.',
        }
    });
    return g;
}

/* Videos keep decoding even when their screen is culled — register them so
   room changes and tab visibility can pause/resume the decode. */
const videoScreens = [];
function chainVisible(o) {
    for (let n = o; n; n = n.parent) if (n.visible === false) return false;
    return true;
}
function syncVideos() {
    for (const v of videoScreens) {
        const on = !document.hidden && chainVisible(v.mesh);
        if (on && v.el.paused) v.el.play().catch(() => { });
        else if (!on && !v.el.paused) v.el.pause();
    }
}
document.addEventListener('visibilitychange', syncVideos);

/* Slideshow screen (theater + projection wall). */
function slideshowScreen(slides, videoUrl, pos, normal, w, h, { caption = true, speed = 7 } = {}) {
    if (videoUrl) {
        const video = document.createElement('video');
        video.src = videoUrl; video.loop = true; video.muted = true; video.playsInline = true;
        video.play().catch(() => { });
        const vt = new THREE.VideoTexture(video);
        vt.colorSpace = THREE.SRGBColorSpace;
        const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h),
            new THREE.MeshBasicMaterial({ map: vt, toneMapped: true }));
        m.position.copy(pos); m.lookAt(pos.clone().add(normal));
        worldGroup.add(m);
        videoScreens.push({ el: video, mesh: m });
        return m;
    }
    const cw = 1280, ch = Math.round(1280 * h / w);
    const c = document.createElement('canvas'); c.width = cw; c.height = ch;
    const g = c.getContext('2d');
    g.fillStyle = '#000'; g.fillRect(0, 0, cw, ch);
    const tex = new THREE.CanvasTexture(c);
    tex.colorSpace = THREE.SRGBColorSpace;
    const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h),
        new THREE.MeshBasicMaterial({ map: tex, toneMapped: true }));
    m.position.copy(pos); m.lookAt(pos.clone().add(normal));
    worldGroup.add(m);

    const imgs = [];
    slides.forEach((s, i) => {
        const im = new Image();
        im.onload = () => { imgs[i] = im; };
        im.src = s.img;
    });
    slideshows.push({
        mesh: m,
        ctx: g, tex, cw, ch, slides, imgs, caption, speed, t: 0, idx: 0, frame: 0,
        draw(dt) {
            this.t += dt; this.frame++;
            if (this.frame % 2) return;                    // half-rate texture upload
            const n = this.slides.length;
            if (!n) return;
            const per = this.speed, fade = 1.4;
            const i = Math.floor(this.t / per) % n, next = (i + 1) % n;
            const tIn = this.t - Math.floor(this.t / per) * per;
            const gg = this.ctx;
            gg.fillStyle = '#000'; gg.fillRect(0, 0, this.cw, this.ch);
            const drawSlide = (k, alpha, phase) => {
                const im = this.imgs[k]; if (!im) return;
                gg.globalAlpha = alpha;
                const sc = 1.04 + 0.05 * phase;
                const iw = this.cw * sc, ih = iw * im.height / im.width;
                const ihMin = this.ch * sc;
                const fh = Math.max(ih, ihMin), fw = fh * im.width / im.height;
                gg.drawImage(im, (this.cw - fw) / 2 - phase * 30, (this.ch - fh) / 2, fw, fh);
                gg.globalAlpha = 1;
            };
            drawSlide(i, 1, tIn / per);
            if (tIn > per - fade) drawSlide(next, (tIn - (per - fade)) / fade, 0);
            if (this.caption && this.slides[i]) {
                gg.fillStyle = 'rgba(0,0,0,.55)'; gg.fillRect(0, this.ch - 84, this.cw, 84);
                gg.fillStyle = '#f4f1ea'; gg.font = '600 40px Georgia, serif'; gg.textBaseline = 'middle';
                gg.fillText(this.slides[i].t || '', 40, this.ch - 42);
            }
            this.tex.needsUpdate = true;
        }
    });
    return m;
}

/* ------------------------------------------------- extended display builders */
const ACCENT = {
    crimson: 0x98002e, gold: 0xe4a524, teal: 0x2a6d81, violet: 0x7c5cbf,
    green: 0x4e7d3a, ochre: 0xb0772e, slate: 0x4a5a6a, rust: 0xb0592e,
};
function accentHex(name) { return ACCENT[name] ?? 0x98002e; }

/* Round column with a plain base + capital (structural realism for big halls). */
function column(x, z, h, r = 0.34, mat = MAT.plaster || MAT.wall) {
    const g = new THREE.Group(); g.position.set(x, 0, z); worldGroup.add(g);
    const shaft = new THREE.Mesh(new THREE.CylinderGeometry(r, r, h - 0.24, 24), mat);
    shaft.position.y = h / 2; shaft.castShadow = true; shaft.receiveShadow = true; g.add(shaft);
    const base = new THREE.Mesh(new THREE.CylinderGeometry(r * 1.28, r * 1.4, 0.14, 24), mat);
    base.position.y = 0.07; base.castShadow = true; g.add(base);
    const cap = new THREE.Mesh(new THREE.CylinderGeometry(r * 1.4, r * 1.28, 0.14, 24), mat);
    cap.position.y = h - 0.07; g.add(cap);
    addCollider(x - r - 0.05, x + r + 0.05, z - r - 0.05, z + r + 0.05);
    return g;
}

/* Coffered ceiling: a grid of shallow beams under a ceiling plane, with optional
   emissive skylight panels between beams — lights the long halls from above. */
function cofferedCeiling(minX, maxX, minZ, maxZ, y, { beam = MAT.frameWood, ceil = MAT.ceiling, bay = 4, skylight = false } = {}) {
    ceilRect(minX, maxX, minZ, maxZ, y, ceil);
    const beamMat = beam, bh = 0.28, bt = 0.16;
    for (let x = minX; x <= maxX + 0.01; x += bay) {
        const b = new THREE.Mesh(new THREE.BoxGeometry(bt, bh, maxZ - minZ), beamMat);
        b.position.set(x, y - bh / 2, (minZ + maxZ) / 2); b.receiveShadow = true; b.castShadow = true;
        worldGroup.add(b);
    }
    for (let z = minZ; z <= maxZ + 0.01; z += bay) {
        const b = new THREE.Mesh(new THREE.BoxGeometry(maxX - minX, bh, bt), beamMat);
        b.position.set((minX + maxX) / 2, y - bh / 2, z); b.receiveShadow = true; b.castShadow = true;
        worldGroup.add(b);
    }
    if (skylight) {
        // bright daylight wells recessed above the beams (sky-white, warm edges)
        const skyMat = new THREE.MeshBasicMaterial({ color: 0xeaf1ff });
        for (let x = minX + bay / 2; x < maxX; x += bay) {
            for (let z = minZ + bay / 2; z < maxZ; z += bay) {
                // warm reveal reveal around a bright sky panel, recessed just below the beams
                const well = new THREE.Mesh(new THREE.PlaneGeometry(bay * 0.72, bay * 0.72),
                    new THREE.MeshBasicMaterial({ color: 0xe7d3a6 }));
                well.rotation.x = Math.PI / 2; well.position.set(x, y - 0.015, z); worldGroup.add(well);
                const s = new THREE.Mesh(new THREE.PlaneGeometry(bay * 0.6, bay * 0.6), skyMat);
                s.rotation.x = Math.PI / 2; s.position.set(x, y - 0.03, z); worldGroup.add(s);
            }
        }
    }
}

/* Emissive cove strip — the colored ceiling wash of the accent galleries. */
function coveLight(x1, x2, z1, z2, y, color) {
    const w = Math.max(Math.abs(x2 - x1), 0.1), d = Math.max(Math.abs(z2 - z1), 0.1);
    const m = new THREE.Mesh(new THREE.BoxGeometry(w || 0.1, 0.05, d || 0.1),
        new THREE.MeshBasicMaterial({ color }));
    m.position.set((x1 + x2) / 2, y, (z1 + z2) / 2);
    worldGroup.add(m);
    return m;
}

/* Glowing neon text on a wall (canvas emissive, with soft bloom baked in). */
function neonText(text, x, y, z, normal, color = 0xffd24a) {
    const pad = 40, fs = 130;
    const c = document.createElement('canvas');
    const measure = c.getContext('2d'); measure.font = `700 ${fs}px Arial, sans-serif`;
    const lines = String(text).split('\n');
    const tw = Math.max(...lines.map(l => measure.measureText(l).width));
    c.width = Math.ceil(tw + pad * 2); c.height = Math.ceil(lines.length * fs * 1.15 + pad * 2);
    const g = c.getContext('2d');
    g.font = `700 ${fs}px Arial, sans-serif`; g.textBaseline = 'top';
    const hex = '#' + color.toString(16).padStart(6, '0');
    g.shadowColor = hex; g.shadowBlur = 46; g.fillStyle = hex;
    for (let pass = 0; pass < 3; pass++) lines.forEach((l, i) => g.fillText(l, pad, pad + i * fs * 1.15));
    g.shadowBlur = 0; g.fillStyle = '#fff7e0';
    lines.forEach((l, i) => g.fillText(l, pad, pad + i * fs * 1.15));
    const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace;
    const scale = 0.02;
    const m = new THREE.Mesh(new THREE.PlaneGeometry(c.width * scale, c.height * scale),
        new THREE.MeshBasicMaterial({ map: tex, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false, toneMapped: false }));
    m.position.set(x, y, z); m.lookAt(x + normal.x, y, z + normal.z);
    worldGroup.add(m);
    return m;
}

/* Standing portrait monolith — a tall emissive lightbox banner on a base, the
   duotone portrait totems of the Hall of Figures (see reference image 1). */
function monolith(item, x, z, ry, color = 0x2a6d81) {
    const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g);
    const W = 0.94, H = 3.1, D = 0.12;
    const base = new THREE.Mesh(new THREE.BoxGeometry(W + 0.1, 0.14, D + 0.5), MAT.frameBlack);
    base.position.y = 0.07; base.castShadow = true; base.receiveShadow = true; g.add(base);
    const slab = new THREE.Mesh(new THREE.BoxGeometry(W, H, D), MAT.frameBlack);
    slab.position.y = 0.14 + H / 2; slab.castShadow = true; g.add(slab);
    const faceZ = D / 2 + 0.006, cy = 0.14 + H / 2;
    // portrait (upper ~78%), duotone via accent-tinted emissive + a multiply wash
    const portH = H * 0.78, portY = cy + H * 0.5 - portH / 2 - 0.06;
    const pmat = new THREE.MeshStandardMaterial({
        map: placeholderArt, roughness: 0.5, metalness: 0, envMapIntensity: 0.2,
        emissive: color, emissiveMap: placeholderArt, emissiveIntensity: 0.9,
    });
    const port = new THREE.Mesh(new THREE.PlaneGeometry(W - 0.04, portH), pmat);
    port.position.set(0, portY, faceZ); g.add(port);
    const tint = new THREE.Mesh(new THREE.PlaneGeometry(W - 0.04, portH),
        new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.42, blending: THREE.MultiplyBlending, depthWrite: false }));
    tint.position.set(0, portY, faceZ + 0.002); g.add(tint);
    // name plate (lower strip)
    const plate = canvasTexture(512, 150, (gg, w, h) => {
        gg.fillStyle = '#' + color.toString(16).padStart(6, '0'); gg.fillRect(0, 0, w, h);
        gg.fillStyle = 'rgba(255,255,255,.92)'; gg.font = '700 52px Arial, sans-serif';
        gg.textAlign = 'center'; gg.textBaseline = 'middle';
        const nm = (item.n || '').toUpperCase();
        gg.font = `700 ${nm.length > 18 ? 40 : 52}px Arial, sans-serif`;
        gg.fillText(nm, w / 2, h / 2);
    });
    const plateH = H * 0.18;
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(W - 0.02, plateH),
        new THREE.MeshStandardMaterial({ map: plate, emissive: 0xffffff, emissiveMap: plate, emissiveIntensity: 0.5, roughness: 0.6 }));
    pl.position.set(0, cy - H * 0.5 + plateH / 2 + 0.05, faceZ); g.add(pl);

    if (item.img) {
        artQueue.push({
            url: item.img, hero: true, pos: new THREE.Vector3(x, 1.8, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
                pmat.map = tex; pmat.emissiveMap = tex; pmat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = (W - 0.04) / portH;
                if (a > target) port.scale.set(1, target / a, 1); else port.scale.set(a / target, 1, 1);
            }
        });
    }
    addCollider(x - W / 2 - 0.2, x + W / 2 + 0.2, z - 0.4, z + 0.4);
    interactables.push({ mesh: port, data: { kind: 'monolith', gallery: item.group || 'Hall of Figures', ...item } });
    return g;
}

/* Dense photo-mosaic wall — hundreds of small emissive portrait tiles, packed
   like contact sheets, backing the Hall of Figures (reference image 1). */
function mosaicWall(items, cx, cy, cz, width, height, normal, link) {
    const g = new THREE.Group(); g.position.set(cx, cy, cz);
    g.lookAt(cx + normal.x, cy, cz + normal.z);
    worldGroup.add(g);
    const backer = new THREE.Mesh(new THREE.PlaneGeometry(width, height),
        new THREE.MeshStandardMaterial({ color: 0x14151a, roughness: 0.9 }));
    backer.position.z = -0.02; g.add(backer);
    /* One mesh + one canvas atlas per panel instead of a mesh/material/texture
       per tile: a 25x9 panel went from ~225 draw calls and up to 225 retained
       full-size textures to 1 draw call and 1 canvas. Tiles render at ~10px on
       screen, so cells are painted from 64px server thumbs. */
    const tile = 0.34, gap = 0.02, CELL = 64, PAD = 2;
    const cols = Math.max(1, Math.floor(width / (tile + gap)));
    const rows = Math.max(1, Math.floor(height / (tile + gap)));
    const canvasEl = document.createElement('canvas');
    canvasEl.width = cols * CELL; canvasEl.height = rows * CELL;
    const ctx = canvasEl.getContext('2d');
    ctx.fillStyle = '#101116'; ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
    ctx.fillStyle = '#1a1c22';
    for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++)
        ctx.fillRect(c * CELL + PAD, r * CELL + PAD, CELL - PAD * 2, CELL - PAD * 2);
    const tex = new THREE.CanvasTexture(canvasEl);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.anisotropy = ANISO_LOW;
    tex.generateMipmaps = false;                 // NPOT canvas: skip per-flush mip rebuilds
    tex.minFilter = THREE.LinearFilter;
    const pw = cols * (tile + gap) - gap, ph = rows * (tile + gap) - gap;
    const panel = new THREE.Mesh(new THREE.PlaneGeometry(pw, ph),
        new THREE.MeshStandardMaterial({ map: tex, roughness: 0.8, emissive: 0xffffff, emissiveMap: tex, emissiveIntensity: 0.34 }));
    g.add(panel);
    g.updateMatrixWorld(true);
    const wallPos = g.localToWorld(new THREE.Vector3(0, 0, 0));
    let idx = 0;
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const rec = items[idx % items.length]; idx++;
            if (!rec || !rec.img) continue;
            const cellX = c * CELL, cellY = (rows - 1 - r) * CELL;   // canvas y runs top-down
            artQueue.push({
                img: true, url: thumbUrl(rec.img, 64), pos: wallPos, low: true,
                apply: (im) => {
                    const w = CELL - PAD * 2;
                    const sc = Math.max(w / im.width, w / im.height);
                    const dw = im.width * sc, dh = im.height * sc;
                    ctx.save();
                    ctx.beginPath(); ctx.rect(cellX + PAD, cellY + PAD, w, w); ctx.clip();
                    ctx.drawImage(im, cellX + PAD + (w - dw) / 2, cellY + PAD + (w - dh) / 2, dw, dh);
                    ctx.restore();
                    atlasDirty.add(tex);
                },
            });
        }
    }
    // one interactable slab over the whole wall
    const hit = new THREE.Mesh(new THREE.PlaneGeometry(width, height),
        new THREE.MeshBasicMaterial({ visible: false }));
    hit.position.z = 0.02; g.add(hit);
    interactables.push({ mesh: hit, data: { kind: 'panel', n: 'Every face in the database', l1: 'The Collection', d: `Each tile is one documented political prisoner. The full database holds ${(DATA.stats && DATA.stats.total) || 'thousands'} of them across American history.`, u: link || '/database' } });
    return g;
}

/* Banner suspended from the ceiling on cables — the dramatic hanging panels of
   the darkened, colored-light galleries. */
function hangingBanner(item, x, topY, z, w, h, normal, { emissive = 0.5 } = {}) {
    const g = new THREE.Group(); g.position.set(x, 0, z);
    g.lookAt(x + normal.x, 0, z + normal.z);
    worldGroup.add(g);
    const cy = topY - h / 2 - 0.4;
    for (const sx of [-w / 2 + 0.1, w / 2 - 0.1]) {
        const cable = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.006, 0.8),
            new THREE.MeshStandardMaterial({ color: 0x222222, metalness: 0.8, roughness: 0.4 }));
        cable.position.set(sx, topY - 0.4, 0); g.add(cable);
    }
    const mat = new THREE.MeshStandardMaterial({
        map: placeholderArt, roughness: 0.8, side: THREE.DoubleSide,
        emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: emissive,
    });
    const panel = new THREE.Mesh(new THREE.PlaneGeometry(w, h), mat);
    panel.position.set(0, cy, 0); panel.castShadow = true; g.add(panel);
    if (item.img) {
        artQueue.push({
            url: item.img, hero: true, pos: new THREE.Vector3(x, cy, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
                mat.map = tex; mat.emissiveMap = tex; mat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = w / h;
                if (a > target) panel.scale.set(1, target / a, 1); else panel.scale.set(a / target, 1, 1);
            }
        });
    }
    interactables.push({ mesh: panel, data: { kind: 'art', ...item } });
    return g;
}

/* Matte plaster-cast human figure (abstracted) — the ghostly white standees of
   the tableau room. Not a real person; a memorial silhouette. */
function plasterFigure(x, z, ry, { seated = false, scale = 1, bronze = false } = {}) {
    const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g);
    const mat = bronze
        ? new THREE.MeshStandardMaterial({ color: 0x6d4f2a, metalness: 0.9, roughness: 0.4, envMapIntensity: 1.3 })
        : new THREE.MeshStandardMaterial({ color: 0xdcdad2, roughness: 0.95, metalness: 0, envMapIntensity: 0.3 });
    const s = scale;
    const legH = seated ? 0.5 : 0.9;
    const hip = seated ? 0.55 : 0.95;
    // legs
    for (const lx of [-0.11, 0.11]) {
        const leg = new THREE.Mesh(new THREE.CapsuleGeometry(0.09 * s, legH * s, 4, 8), mat);
        leg.position.set(lx * s, (seated ? 0.25 : 0.5) * s, seated ? 0.18 * s : 0); leg.rotation.x = seated ? -1.3 : 0;
        leg.castShadow = true; g.add(leg);
    }
    const torso = new THREE.Mesh(new THREE.CapsuleGeometry(0.17 * s, 0.5 * s, 4, 10), mat);
    torso.position.set(0, (hip + 0.35) * s, seated ? 0.02 * s : 0); torso.castShadow = true; g.add(torso);
    const head = new THREE.Mesh(new THREE.SphereGeometry(0.13 * s, 16, 16), mat);
    head.position.set(0, (hip + 0.78) * s, seated ? 0.02 * s : 0); head.castShadow = true; g.add(head);
    for (const ax of [-0.28, 0.28]) {
        const arm = new THREE.Mesh(new THREE.CapsuleGeometry(0.06 * s, 0.5 * s, 4, 8), mat);
        arm.position.set(ax * s, (hip + 0.3) * s, seated ? 0.05 * s : 0.02 * s); arm.rotation.z = ax < 0 ? 0.18 : -0.18;
        arm.castShadow = true; g.add(arm);
    }
    addCollider(x - 0.35, x + 0.35, z - 0.35, z + 0.35);
    return g;
}

/* Single upholstered cinema seat, facing -X (toward the screen). */
function cinemaSeat(x, y, z, accent) {
    const g = new THREE.Group(); g.position.set(x, y, z); worldGroup.add(g);
    const body = new THREE.MeshStandardMaterial({ color: 0x33353b, roughness: 0.7, envMapIntensity: 0.4 });
    const acc = new THREE.MeshStandardMaterial({ color: accent, roughness: 0.7 });
    const pan = new THREE.Mesh(new THREE.BoxGeometry(0.52, 0.1, 0.6), body);
    pan.position.set(0, 0.47, 0); pan.castShadow = true; pan.receiveShadow = true; g.add(pan);
    const back = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.66, 0.58), body);
    back.position.set(0.26, 0.78, 0); back.castShadow = true; g.add(back);
    for (const az of [-0.31, 0.31]) {
        const arm = new THREE.Mesh(new THREE.BoxGeometry(0.44, 0.1, 0.07), Math.abs(az) > 0.3 && az > 0 ? acc : body);
        arm.position.set(-0.02, 0.6, az); g.add(arm);
        const post = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.34, 0.06), new THREE.MeshStandardMaterial({ color: 0x1a1b1e, metalness: 0.6, roughness: 0.4 }));
        post.position.set(-0.02, 0.3, az); g.add(post);
    }
    return g;
}

/* ================================================================= LAYOUT */
const CEIL = { rotunda: 7, spine: 7.6, gallery: 6, archive: 3.8, cinema: 5.2, cell: 2.6, reading: 4.2 };

/* Shared refs the render loop / lighting reach into. */
let cellLight = null;
const anchors = {};

/* Themed materials for the accent (dark, dramatic) galleries. */
const DARKWALL = new THREE.MeshStandardMaterial({ ...pbr('plaster', { repeat: [3, 1.6] }), color: 0x2a2c31, envMapIntensity: 0.14 });
/* Warm mass-timber beams for the coffered ceilings. */
const TIMBER = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [0.5, 1.2] }), color: 0xcaa066, roughness: 0.62, envMapIntensity: 0.5 });

/* Hang a themed gallery's works across its three solid walls. sign=+1 east, -1 west. */
/* Ceiling track with several visible spotlight fixtures (visual; the pools come
   from the per-artwork washes + room key light). */
function trackLight(x1, x2, z, y, n = 5) {
    const len = Math.abs(x2 - x1);
    const rail = new THREE.Mesh(new THREE.BoxGeometry(len, 0.05, 0.05), new THREE.MeshStandardMaterial({ color: 0x1c1d20, metalness: 0.6, roughness: 0.4 }));
    rail.position.set((x1 + x2) / 2, y - 0.03, z); worldGroup.add(rail);
    for (let i = 0; i < n; i++) {
        const fx = x1 + (i + 0.5) * (x2 - x1) / n;
        const head = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.06, 0.16, 12), new THREE.MeshStandardMaterial({ color: 0x141518, metalness: 0.5, roughness: 0.5 }));
        head.position.set(fx, y - 0.14, z); head.rotation.z = 0.5; worldGroup.add(head);
        const lens = new THREE.Mesh(new THREE.CircleGeometry(0.045, 12), new THREE.MeshBasicMaterial({ color: 0xfff2d4 }));
        lens.position.set(fx + 0.06, y - 0.2, z); lens.rotation.x = -Math.PI / 2 + 0.5; worldGroup.add(lens);
    }
}
function trackLightZ(z1, z2, x, y, n = 5) {
    const len = Math.abs(z2 - z1);
    const rail = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.05, len), new THREE.MeshStandardMaterial({ color: 0x1c1d20, metalness: 0.6, roughness: 0.4 }));
    rail.position.set(x, y - 0.03, (z1 + z2) / 2); worldGroup.add(rail);
    for (let i = 0; i < n; i++) {
        const fz = z1 + (i + 0.5) * (z2 - z1) / n;
        const head = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.06, 0.16, 12), new THREE.MeshStandardMaterial({ color: 0x141518, metalness: 0.5, roughness: 0.5 }));
        head.position.set(x, y - 0.14, fz); head.rotation.x = 0.5; worldGroup.add(head);
    }
}

/* Gold section lettering on a wall (the "BUDDHA / CONTEMPORARY" title walls). */
function goldLettering(title, x, y, z, normal, size = 1.7) {
    const c = document.createElement('canvas');
    const m0 = c.getContext('2d'); m0.font = '700 130px Georgia, serif';
    const words = String(title).toUpperCase().split(' ');
    const lines = []; let cur = '';
    for (const w of words) { if ((cur + ' ' + w).length > 16 && cur) { lines.push(cur); cur = w; } else cur = cur ? cur + ' ' + w : w; }
    if (cur) lines.push(cur);
    const tw = Math.max(...lines.map(l => m0.measureText(l).width));
    c.width = Math.ceil(tw + 40); c.height = Math.ceil(lines.length * 150 + 40);
    const g = c.getContext('2d');
    g.font = '700 130px Georgia, serif'; g.textBaseline = 'top';
    const grd = g.createLinearGradient(0, 0, 0, c.height);
    grd.addColorStop(0, '#f0d488'); grd.addColorStop(0.5, '#c9a23c'); grd.addColorStop(1, '#8f6f1e');
    g.fillStyle = grd;
    lines.forEach((l, i) => g.fillText(l, 20, 20 + i * 150));
    const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = ANISO;
    const scale = size / lines.length / 1.3;
    const mesh = new THREE.Mesh(new THREE.PlaneGeometry(c.width * scale * 0.01, c.height * scale * 0.01),
        new THREE.MeshStandardMaterial({ map: tex, transparent: true, roughness: 0.35, metalness: 0.7, emissive: 0x3a2c0a, emissiveMap: tex, emissiveIntensity: 0.25, side: THREE.DoubleSide }));
    mesh.position.set(x, y, z); mesh.lookAt(x + normal.x, y, z + normal.z);
    worldGroup.add(mesh);
    return mesh;
}

/* Freestanding partition wall (a movable gallery wall) running along Z at x=px,
   art hung on the door-facing face. Returns the face-normal used. */
function partitionWall(px, cz, half, h, faceSign, mat) {
    const wall = new THREE.Mesh(new THREE.BoxGeometry(0.22, h, half * 2), mat);
    wall.position.set(px, h / 2, cz); wall.castShadow = true; wall.receiveShadow = true;
    worldGroup.add(wall);
    addCollider(px - 0.2, px + 0.2, cz - half, cz + half);
    return new THREE.Vector3(faceSign, 0, 0);
}

/* Open-hall gallery hang (reference image 2/4): a salon cluster on the far
   outer wall with a big gold section title, plus two freestanding partition
   fins standing in the open floor holding art on both faces. Edge-lit frames. */
function fillOpenGallery(items, sign, innerX, outerX, cxMid, zLo, zHi, doorZ, frameMat, accent, title) {
    let k = 0;
    const next = () => items[k++];
    const outN = new THREE.Vector3(-sign, 0, 0);
    const outX = outerX - sign * 0.18;
    const finMat = new THREE.MeshStandardMaterial({ ...pbr('plaster', { repeat: [1.4, 1] }), color: 0xece7dd, envMapIntensity: 0.2 });

    // gold section title high on the outer wall
    goldLettering(title, outX, 4.7, doorZ, outN, 1.7);
    // five large, well-spaced framed works on the outer wall (museum scale)
    const cluster = [
        [doorZ - 5.2, 1.7, 1.7], [doorZ - 2.6, 1.7, 1.5], [doorZ, 1.7, 1.8],
        [doorZ + 2.6, 1.7, 1.5], [doorZ + 5.2, 1.7, 1.7],
    ];
    cluster.forEach(([z, y, h]) => { const it = next(); if (it) hangArt(it, new THREE.Vector3(outX, y, z), outN, { frame: frameMat, artH: h, edge: accent }); });

    // two freestanding fins standing in the open floor, art on both faces
    [sign * 12.5, sign * 18.5].forEach((px) => {
        partitionWall(px, doorZ, 3.4, 3.6, -sign, finMat);
        [doorZ - 1.9, doorZ + 1.9].forEach(z => { const it = next(); if (it) hangArt(it, new THREE.Vector3(px - sign * 0.14, 1.55, z), new THREE.Vector3(-sign, 0, 0), { frame: frameMat, artH: 1.35, edge: accent }); });
        [doorZ - 1.9, doorZ + 1.9].forEach(z => { const it = next(); if (it) hangArt(it, new THREE.Vector3(px + sign * 0.14, 1.55, z), new THREE.Vector3(sign, 0, 0), { frame: frameMat, artH: 1.25, edge: accent }); });
    });

    // a couple on each end wall
    [-3, 3].forEach(dx => { const it = next(); if (it) hangArt(it, new THREE.Vector3(cxMid + dx, 1.6, zLo + 0.18), new THREE.Vector3(0, 0, 1), { frame: frameMat, artH: 1.2, edge: accent }); });
    [-3, 3].forEach(dx => { const it = next(); if (it) hangArt(it, new THREE.Vector3(cxMid + dx, 1.6, zHi - 0.18), new THREE.Vector3(0, 0, -1), { frame: frameMat, artH: 1.15, edge: accent }); });
}

/* ======================================================================
   ENTRANCE SEQUENCE v3 — exterior plaza → glazed facade → grand atrium.
   A double-height mass-timber atrium with skylight wells, a monumental
   travertine stair to a walkable mezzanine, a reflecting pond with a
   waterfall, ficus trees, and a cascade of hanging light rods.
   ====================================================================== */
const animatedTex = [];                  // per-frame texture updaters (waterfall)

/* --- small nature/architecture builders ------------------------------ */
/* ---------------------------------------------------- realistic foliage ---
   Layered tropical planting like a museum atrium bed (cf. the Guggenheim
   lobby): arching palm fronds and broad paddle leaves in the centre, sword
   blades mid-height, variegated groundcover spilling over a low white rim,
   all over moss. Leaves are folded shape-geometry (not billboards) so they
   catch the spotlights. Every plant's leaves are merged into one geometry
   per material, so a whole bed is only a handful of draw calls. */
const TAU = Math.PI * 2;
const POT_MAT = new THREE.MeshStandardMaterial({ color: 0xdcd5c7, roughness: 0.5, envMapIntensity: 0.5 });
const PLANTER_MAT = new THREE.MeshStandardMaterial({ color: 0xeee9df, roughness: 0.55, envMapIntensity: 0.4 });
const MOSS_MAT = new THREE.MeshStandardMaterial({ color: 0x33502c, roughness: 1, envMapIntensity: 0.08 });
const STEM_MAT = new THREE.MeshStandardMaterial({ color: 0x50602f, roughness: 0.85, envMapIntensity: 0.2 });
const LEAF_GREEN_MAT = new THREE.MeshStandardMaterial({ color: 0xffffff, vertexColors: true, roughness: 0.44, side: THREE.DoubleSide, envMapIntensity: 0.6 });
const LEAF_VARIE_MAT = new THREE.MeshStandardMaterial({ color: 0xffffff, vertexColors: true, roughness: 0.5, side: THREE.DoubleSide, envMapIntensity: 0.55 });
const GREENS = [[0.15, 0.31, 0.16], [0.21, 0.42, 0.20], [0.12, 0.26, 0.15], [0.27, 0.47, 0.23], [0.17, 0.37, 0.21]];
const VARIES = [[0.55, 0.67, 0.41], [0.67, 0.75, 0.52], [0.43, 0.59, 0.33]];
const greenColor = () => GREENS[Math.random() * GREENS.length | 0];
const varieColor = () => VARIES[Math.random() * VARIES.length | 0];
const STEM_UNIT = new THREE.CylinderGeometry(1, 1, 1, 5);

// folded leaf geometry: base at origin, tip at +y, unit length; fold curls it
// around the midrib so it isn't a flat card.
function buildLeafGeo(W, { tipFrac = 0.68, fold = 0.32, seg = 10 } = {}) {
    const s = new THREE.Shape();
    s.moveTo(0, 0);
    s.bezierCurveTo(W * 0.5, 0.22, W * 0.28, tipFrac, W * 0.05, 0.95);
    s.quadraticCurveTo(0, 1.0, -W * 0.05, 0.95);
    s.bezierCurveTo(-W * 0.28, tipFrac, -W * 0.5, 0.22, 0, 0);
    const geo = new THREE.ShapeGeometry(s, seg);
    const p = geo.attributes.position;
    for (let i = 0; i < p.count; i++) p.setZ(i, -Math.abs(p.getX(i)) * fold);
    geo.computeVertexNormals();
    return geo;
}
const LEAFGEO = {
    broad: buildLeafGeo(0.62, { fold: 0.34 }),            // banana / bird-of-paradise
    heart: buildLeafGeo(0.82, { fold: 0.2, tipFrac: 0.58 }), // elephant ear
    blade: buildLeafGeo(0.12, { fold: 0.55, tipFrac: 0.86 }), // snake plant / sword
    leaflet: buildLeafGeo(0.17, { fold: 0.3, tipFrac: 0.82 }), // palm leaflet
    round: buildLeafGeo(0.44, { fold: 0.16, tipFrac: 0.5 }), // groundcover
};

const mrotX = a => new THREE.Matrix4().makeRotationX(a);
const mrotY = a => new THREE.Matrix4().makeRotationY(a);
const mrotZ = a => new THREE.Matrix4().makeRotationZ(a);
const mtr = (x, y, z) => new THREE.Matrix4().makeTranslation(x, y, z);
const msc = (x, y, z) => new THREE.Matrix4().makeScale(x, y, z);
const mmul = (...ms) => ms.reduce((a, b) => a.multiply(b), new THREE.Matrix4());

function newPlant() { return { green: [], varie: [], stem: [] }; }
function addPart(p, bucket, geo, matrix, color) { p[bucket].push({ geo, matrix, color }); }
function mergeBucket(parts) {
    if (!parts.length) return null;
    let total = 0;
    const items = parts.map(pt => {
        const g = pt.geo.index ? pt.geo.toNonIndexed() : pt.geo.clone();
        g.applyMatrix4(pt.matrix);
        if (!g.attributes.normal) g.computeVertexNormals();
        total += g.attributes.position.count;
        return { g, color: pt.color };
    });
    const pos = new Float32Array(total * 3), nor = new Float32Array(total * 3), col = new Float32Array(total * 3);
    let o = 0;
    for (const { g, color } of items) {
        const cnt = g.attributes.position.count;
        pos.set(g.attributes.position.array, o * 3);
        nor.set(g.attributes.normal.array, o * 3);
        const c = color || [1, 1, 1];
        for (let k = 0; k < cnt; k++) { col[(o + k) * 3] = c[0]; col[(o + k) * 3 + 1] = c[1]; col[(o + k) * 3 + 2] = c[2]; }
        o += cnt; g.dispose();
    }
    const m = new THREE.BufferGeometry();
    m.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    m.setAttribute('normal', new THREE.BufferAttribute(nor, 3));
    m.setAttribute('color', new THREE.BufferAttribute(col, 3));
    return m;
}
function finalizePlant(p, x, y, z) {
    const g = new THREE.Group(); g.position.set(x, y, z);
    for (const [k, mat] of [['green', LEAF_GREEN_MAT], ['varie', LEAF_VARIE_MAT], ['stem', STEM_MAT]]) {
        const geo = mergeBucket(p[k]);
        if (!geo) continue;
        const mesh = new THREE.Mesh(geo, mat);
        mesh.castShadow = true; mesh.receiveShadow = true;
        g.add(mesh);
    }
    worldGroup.add(g);
    return g;
}

// one leaf on an optional petiole, radiating from `base` at azimuth/tilt
function armLeaf(p, base, o) {
    const arm = mmul(base, mrotY(o.az), mrotX(o.tilt));
    if (o.stemLen > 0) addPart(p, 'stem', STEM_UNIT, mmul(arm, mtr(0, o.stemLen / 2, 0), msc(o.stemR, o.stemLen, o.stemR)));
    addPart(p, o.bucket, o.geo, mmul(arm, mtr(0, o.stemLen, 0), mrotX(o.droop), msc(o.scale, o.scale, o.scale)), o.color);
}
// a pinnate palm frond: arching rachis lined with leaflets
function emitFrond(p, base, rl, color) {
    addPart(p, 'stem', STEM_UNIT, mmul(base, mtr(0, rl / 2, 0), msc(0.01, rl, 0.01)));
    const pairs = 8;
    for (let i = 1; i <= pairs; i++) {
        const t = i / (pairs + 1), yy = t * rl, size = (0.85 - t * 0.5) * rl * 1.3;
        for (const side of [-1, 1]) {
            addPart(p, 'green', LEAFGEO.leaflet, mmul(base, mtr(0, yy, 0), mrotZ(side * 1.02), mrotX(0.22), msc(size, size, size)), color);
        }
    }
    addPart(p, 'green', LEAFGEO.leaflet, mmul(base, mtr(0, rl, 0), msc(0.22 * rl, 0.22 * rl, 0.22 * rl)), color);
}
function emitPalm(p, ox, oy, oz, scale = 1) {
    const base = mtr(ox, oy, oz);
    const trunks = 1 + (Math.random() * 3 | 0);
    for (let t = 0; t < trunks; t++) {
        const bx = (Math.random() - 0.5) * 0.36 * scale, bz = (Math.random() - 0.5) * 0.36 * scale;
        const h = (1.6 + Math.random() * 1.0) * scale;
        addPart(p, 'stem', STEM_UNIT, mmul(base, mtr(bx, h / 2, bz), msc(0.045 * scale, h, 0.045 * scale)));
        const crown = mmul(base, mtr(bx, h, bz));
        const fronds = 6 + (Math.random() * 3 | 0), col = greenColor();
        for (let f = 0; f < fronds; f++) {
            emitFrond(p, mmul(crown, mrotY((f / fronds) * TAU + Math.random() * 0.4), mrotX(0.35 + Math.random() * 0.55)), (0.85 + Math.random() * 0.5) * scale, col);
        }
    }
}
function emitBroad(p, ox, oy, oz, scale = 1, geo = LEAFGEO.broad) {
    const base = mtr(ox, oy, oz), n = 6 + (Math.random() * 3 | 0);
    for (let i = 0; i < n; i++) {
        armLeaf(p, base, { az: (i / n) * TAU + Math.random() * 0.5, tilt: 0.35 + (i / n) * 0.55 + Math.random() * 0.12,
            stemLen: (0.45 + Math.random() * 0.5) * scale, stemR: 0.02 * scale, geo, bucket: 'green', color: greenColor(),
            droop: 0.3 + Math.random() * 0.25, scale: (0.85 + Math.random() * 0.5) * scale });
    }
}
function emitBlade(p, ox, oy, oz, scale = 1, variegate = false) {
    const base = mtr(ox, oy, oz), n = 7 + (Math.random() * 5 | 0);
    for (let i = 0; i < n; i++) {
        const varie = variegate && Math.random() < 0.4;
        armLeaf(p, base, { az: Math.random() * TAU, tilt: Math.random() * 0.28, stemLen: 0, stemR: 0,
            geo: LEAFGEO.blade, bucket: varie ? 'varie' : 'green', color: varie ? varieColor() : greenColor(),
            droop: 0.05, scale: (0.5 + Math.random() * 0.6) * scale });
    }
}
function emitGround(p, ox, oy, oz, scale = 1) {
    const base = mtr(ox, oy, oz), n = 6 + (Math.random() * 4 | 0);
    for (let i = 0; i < n; i++) {
        const varie = Math.random() < 0.5;
        armLeaf(p, base, { az: Math.random() * TAU, tilt: 0.7 + Math.random() * 0.5, stemLen: 0.1 * scale, stemR: 0.012 * scale,
            geo: LEAFGEO.round, bucket: varie ? 'varie' : 'green', color: varie ? varieColor() : greenColor(),
            droop: 0.5 + Math.random() * 0.3, scale: (0.26 + Math.random() * 0.2) * scale });
    }
}

// small leafy clump (used on the mezzanine planter boxes)
function bushSprite(x, y, z, s = 1) {
    const p = newPlant();
    emitGround(p, 0, y, 0, s * 1.6);
    emitGround(p, 0.22 * s, y, 0.16 * s, s * 1.1);
    finalizePlant(p, x, 0, z);
}
// potted specimen tree
function ficusTree(x, z, h = 4, potR = 0.55) {
    const potH = potR;
    const pot = new THREE.Mesh(new THREE.CylinderGeometry(potR, potR * 0.8, potH, 22), POT_MAT);
    pot.position.set(x, potH / 2, z); pot.castShadow = true; pot.receiveShadow = true; worldGroup.add(pot);
    const soil = new THREE.Mesh(new THREE.CircleGeometry(potR * 0.82, 20), MOSS_MAT);
    soil.rotation.x = -Math.PI / 2; soil.position.set(x, potH + 0.005, z); worldGroup.add(soil);
    const p = newPlant();
    if (h >= 3.6) emitPalm(p, 0, potH, 0, h / 3.0);
    else emitBroad(p, 0, potH, 0, h / 3.2, LEAFGEO.heart);
    for (let i = 0; i < 5; i++) emitGround(p, Math.cos(i * 1.3) * potR * 0.55, potH, Math.sin(i * 1.3) * potR * 0.55, 0.6);
    finalizePlant(p, x, 0, z);
    addCollider(x - potR, x + potR, z - potR, z + potR);
}
// low white planter filled with a layered composition
function planterBed(x0, x1, z0, z1, nBush = 4) {
    const w = x1 - x0, d = z1 - z0, cx = (x0 + x1) / 2, cz = (z0 + z1) / 2;
    const curb = new THREE.Mesh(new THREE.BoxGeometry(w, 0.4, d), PLANTER_MAT);
    curb.position.set(cx, 0.2, cz); curb.castShadow = true; curb.receiveShadow = true; worldGroup.add(curb);
    const cap = new THREE.Mesh(new THREE.BoxGeometry(w + 0.08, 0.06, d + 0.08), PLANTER_MAT);
    cap.position.set(cx, 0.4, cz); cap.castShadow = true; worldGroup.add(cap);
    const soil = new THREE.Mesh(new THREE.BoxGeometry(w - 0.14, 0.05, d - 0.14), MOSS_MAT);
    soil.position.set(cx, 0.42, cz); soil.receiveShadow = true; worldGroup.add(soil);

    const p = newPlant();
    const hx = (w - 0.4) / 2, hz = (d - 0.4) / 2, baseY = 0.44;
    const rnd = h => (Math.random() * 2 - 1) * h;
    const span = Math.max(hx, hz), cScale = Math.min(1.15, span * 0.35 + 0.55);
    emitPalm(p, rnd(hx * 0.3), baseY, rnd(hz * 0.25), cScale);
    if (span > 0.9) emitBroad(p, rnd(hx * 0.5), baseY, rnd(hz * 0.4), Math.min(1.0, cScale));
    const nBlade = Math.max(2, nBush - 1);
    for (let i = 0; i < nBlade; i++) emitBlade(p, rnd(hx * 0.85), baseY, rnd(hz * 0.85), 0.7 + Math.random() * 0.4, true);
    const nG = nBush + 4;
    for (let i = 0; i < nG; i++) {
        const ex = Math.random() < 0.5 ? -1 : 1, ez = Math.random() < 0.5 ? -1 : 1;
        emitGround(p, ex * hx * (0.55 + Math.random() * 0.4), baseY, ez * hz * (0.55 + Math.random() * 0.4), 0.9 + Math.random() * 0.5);
    }
    finalizePlant(p, cx, 0, cz);
    addCollider(x0, x1, z0, z1);
}

/* ------------------------------------------------ boulders & garden tree ---
   High-resolution granite boulders (displaced icosahedra, flat-shaded, with a
   procedural speckled-granite map on planar-projected UVs) and an airy
   branching courtyard tree (recursive bark limbs + hi-res leaf-mass canopy). */
const GRANITE_TEX = canvasTexture(1024, 1024, (g, w, h) => {
    g.fillStyle = '#8c8984'; g.fillRect(0, 0, w, h);
    const blotch = ['#6f6c69', '#a19d97', '#7d7a76', '#b6afa5', '#5e5c5a', '#9a8f80'];
    for (let i = 0; i < 520; i++) {
        g.globalAlpha = 0.22 + Math.random() * 0.4;
        g.fillStyle = blotch[Math.random() * blotch.length | 0];
        const x = Math.random() * w, y = Math.random() * h, r = 5 + Math.random() * 30;
        g.beginPath(); g.ellipse(x, y, r, r * (0.45 + Math.random() * 0.6), Math.random() * 3, 0, TAU); g.fill();
    }
    for (let i = 0; i < 2800; i++) {
        g.globalAlpha = 0.5 + Math.random() * 0.5;
        const c = Math.random();
        g.fillStyle = c < 0.5 ? '#efeae1' : (c < 0.8 ? '#48453f' : '#c7a78d');
        const s = 0.8 + Math.random() * 2.2;
        g.fillRect(Math.random() * w, Math.random() * h, s, s);
    }
    g.globalAlpha = 1;
});
GRANITE_TEX.wrapS = GRANITE_TEX.wrapT = THREE.RepeatWrapping;
const ROCK_MAT = new THREE.MeshStandardMaterial({ map: GRANITE_TEX, color: 0xece7de, roughness: 0.82, metalness: 0.03, flatShading: true, envMapIntensity: 0.55 });

function planarUV(geo, scale) {
    const pos = geo.attributes.position, nrm = geo.attributes.normal;
    const uv = new Float32Array(pos.count * 2);
    for (let i = 0; i < pos.count; i++) {
        const nx = Math.abs(nrm.getX(i)), ny = Math.abs(nrm.getY(i)), nz = Math.abs(nrm.getZ(i));
        const px = pos.getX(i), py = pos.getY(i), pz = pos.getZ(i);
        let u, v;
        if (nx >= ny && nx >= nz) { u = pz; v = py; }
        else if (ny >= nx && ny >= nz) { u = px; v = pz; }
        else { u = px; v = py; }
        uv[i * 2] = u * scale; uv[i * 2 + 1] = v * scale;
    }
    geo.setAttribute('uv', new THREE.BufferAttribute(uv, 2));
}
function makeBoulderGeo(seed) {
    const geo = new THREE.IcosahedronGeometry(1, 2);
    const pos = geo.attributes.position, v = new THREE.Vector3();
    for (let i = 0; i < pos.count; i++) {
        v.set(pos.getX(i), pos.getY(i), pos.getZ(i)).normalize();
        let d = 1;
        d += 0.30 * Math.sin(v.x * 2.1 + seed) * Math.sin(v.y * 2.4 + seed * 1.3) * Math.sin(v.z * 2.7 + seed * 0.7);
        d += 0.16 * Math.sin(v.x * 4.3 + seed * 1.7) * Math.cos(v.z * 4.1 + seed * 0.5);
        d += 0.08 * Math.cos(v.y * 8.1 + seed * 2.2);
        v.multiplyScalar(d);
        pos.setXYZ(i, v.x, v.y, v.z);
    }
    geo.computeVertexNormals();
    planarUV(geo, 0.9);
    return geo;
}
function boulder(x, z, scale, ry, { yBase = 0, collide = true } = {}) {
    const m = new THREE.Mesh(makeBoulderGeo(Math.random() * 100), ROCK_MAT);
    const sy = scale * (0.78 + Math.random() * 0.5);
    m.scale.set(scale * (0.9 + Math.random() * 0.3), sy, scale * (0.9 + Math.random() * 0.3));
    m.rotation.set((Math.random() - 0.5) * 0.28, ry, (Math.random() - 0.5) * 0.28);
    m.position.set(x, yBase + sy * 0.58, z);
    m.castShadow = true; m.receiveShadow = true;
    worldGroup.add(m);
    if (collide) addCollider(x - scale * 0.85, x + scale * 0.85, z - scale * 0.85, z + scale * 0.85);
    return m;
}

const BARK_TEX = canvasTexture(256, 1024, (g, w, h) => {
    g.fillStyle = '#5f5040'; g.fillRect(0, 0, w, h);
    for (let i = 0; i < 4000; i++) {
        g.globalAlpha = 0.15 + Math.random() * 0.4;
        const c = Math.random(); g.fillStyle = c < 0.5 ? '#4a3d30' : (c < 0.8 ? '#73624d' : '#8a7860');
        const x = Math.random() * w, y = Math.random() * h;
        g.fillRect(x, y, 1 + Math.random() * 2, 6 + Math.random() * 40);
    }
    g.globalAlpha = 1;
});
BARK_TEX.wrapS = BARK_TEX.wrapT = THREE.RepeatWrapping;
const BARK_MAT = new THREE.MeshStandardMaterial({ map: BARK_TEX, color: 0x8a7a63, roughness: 0.92, envMapIntensity: 0.18 });
const LEAFMASS_TEX = canvasTexture(1024, 1024, (g, w, h) => {
    g.clearRect(0, 0, w, h);
    const greens = ['#3f6a34', '#4c7d3c', '#5c8f45', '#356030', '#6d9a4f', '#2c5228'];
    for (let i = 0; i < 900; i++) {
        g.save();
        g.globalAlpha = 0.65 + Math.random() * 0.35;
        g.fillStyle = greens[Math.random() * greens.length | 0];
        const x = 40 + Math.random() * (w - 80), y = 40 + Math.random() * (h - 80);
        g.translate(x, y); g.rotate(Math.random() * TAU);
        const lw = 5 + Math.random() * 9, lh = 12 + Math.random() * 22;
        g.beginPath(); g.ellipse(0, 0, lw, lh, 0, 0, TAU); g.fill();
        g.restore();
    }
    g.globalAlpha = 1;
});
const LEAFMASS_MAT = new THREE.MeshStandardMaterial({ map: LEAFMASS_TEX, transparent: true, alphaTest: 0.42, side: THREE.DoubleSide, roughness: 0.72, envMapIntensity: 0.4 });

// airy branching courtyard tree, rooted at (x,0,z)
function gardenTree(x, z, height = 5.4, { lean = 0.12 } = {}) {
    const g = new THREE.Group(); g.position.set(x, 0, z);
    const up = new THREE.Vector3(lean * (Math.random() - 0.5) * 2, 1, lean * (Math.random() - 0.5) * 2).normalize();
    const _q = new THREE.Quaternion(), _yv = new THREE.Vector3(0, 1, 0);
    function limb(pos, dir, len, rad, depth) {
        const geo = new THREE.CylinderGeometry(rad * 0.72, rad, len, 6, 1, false);
        const m = new THREE.Mesh(geo, BARK_MAT);
        m.quaternion.copy(_q.setFromUnitVectors(_yv, dir));
        m.position.copy(pos).addScaledVector(dir, len * 0.5);
        m.castShadow = true; g.add(m);
        const tip = pos.clone().addScaledVector(dir, len);
        if (depth <= 0 || len < 0.5) { canopy(tip, len); return; }
        const n = depth > 2 ? 3 : 2 + (Math.random() * 2 | 0);
        for (let i = 0; i < n; i++) {
            const nd = dir.clone();
            nd.x += (Math.random() - 0.5) * 1.0; nd.z += (Math.random() - 0.5) * 1.0;
            nd.y += 0.12 + Math.random() * 0.35; nd.normalize();
            limb(tip, nd, len * (0.68 + Math.random() * 0.14), rad * 0.66, depth - 1);
        }
    }
    function canopy(pos, size) {
        const s = 1.1 + size * 1.7;
        for (let i = 0; i < 3; i++) {
            const p = new THREE.Mesh(new THREE.PlaneGeometry(s, s * (0.8 + Math.random() * 0.3)), LEAFMASS_MAT);
            p.position.copy(pos).add(new THREE.Vector3((Math.random() - 0.5) * 0.5, (Math.random() - 0.5) * 0.4, (Math.random() - 0.5) * 0.5));
            p.rotation.set((Math.random() - 0.5) * 0.5, i * 1.05 + Math.random() * 0.3, (Math.random() - 0.5) * 0.5);
            p.castShadow = true; g.add(p);
        }
    }
    const trunkLen = height * 0.4, trunkRad = height * 0.03;
    limb(new THREE.Vector3(0, 0, 0), up, trunkLen, trunkRad, 4);
    worldGroup.add(g);
    addCollider(x - 0.35, x + 0.35, z - 0.35, z + 0.35);
    return g;
}
/* Glass balustrade with timber cap rail; collider only bites at its own level. */
function balustrade(axis, fixed, from, to, floorY) {
    const len = Math.abs(to - from), mid = (from + to) / 2;
    const glass = new THREE.Mesh(
        axis === 'x' ? new THREE.PlaneGeometry(len, 0.92) : new THREE.PlaneGeometry(len, 0.92), MAT.glass);
    glass.position.set(axis === 'x' ? mid : fixed, floorY + 0.52, axis === 'x' ? fixed : mid);
    if (axis === 'z') glass.rotation.y = Math.PI / 2;
    worldGroup.add(glass);
    const rail = new THREE.Mesh(
        axis === 'x' ? new THREE.BoxGeometry(len, 0.07, 0.09) : new THREE.BoxGeometry(0.09, 0.07, len), TIMBER);
    rail.position.set(axis === 'x' ? mid : fixed, floorY + 1.02, axis === 'x' ? fixed : mid);
    rail.castShadow = true; worldGroup.add(rail);
    if (axis === 'x') addCollider(Math.min(from, to), Math.max(from, to), fixed - 0.08, fixed + 0.08, floorY - 0.3, floorY + 1.5);
    else addCollider(fixed - 0.08, fixed + 0.08, Math.min(from, to), Math.max(from, to), floorY - 0.3, floorY + 1.5);
}
/* Solid monumental stair running along -z (ascending northward). */
function grandStair(x0, x1, zBottom, zTop, rise, mat) {
    const n = 24, run = zBottom - zTop, dz = run / n, dy = rise / n;
    for (let i = 0; i < n; i++) {
        const zA = zBottom - i * dz, zB = zBottom - (i + 1) * dz;
        const topY = (i + 1) * dy;
        const step = new THREE.Mesh(new THREE.BoxGeometry(x1 - x0, topY, dz + 0.02), mat);
        step.position.set((x0 + x1) / 2, topY / 2, (zA + zB) / 2);
        step.receiveShadow = true; step.castShadow = true;
        worldGroup.add(step);
        addFloorZone(x0, x1, zB, zA, topY);
    }
    // sloped handrail on the open (east) side
    const dir = new THREE.Vector3(0, rise, -(run)).normalize();
    const length = Math.hypot(rise, run);
    const railGeo = new THREE.CylinderGeometry(0.035, 0.035, length, 10);
    const rail = new THREE.Mesh(railGeo, MAT.brass);
    rail.position.set(x1 + 0.12, rise / 2 + 1.0, (zBottom + zTop) / 2);
    rail.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), dir);
    worldGroup.add(rail);
    for (let i = 0; i <= 4; i++) {
        const t = i / 4;
        const post = new THREE.Mesh(new THREE.CylinderGeometry(0.022, 0.022, 1.0, 8), MAT.frameBlack);
        post.position.set(x1 + 0.12, t * rise + 0.5, zBottom - t * run);
        worldGroup.add(post);
    }
}
/* Falling-water sheet: scrolling streak texture + foam. */
function waterfall(x, z, w = 2.0, h = 2.7) {
    const stone = new THREE.Mesh(new THREE.BoxGeometry(w + 0.7, h + 0.4, 0.55),
        new THREE.MeshStandardMaterial({ ...pbr('concrete', { repeat: [1.2, 1.2] }), color: 0x4d5158, envMapIntensity: 0.25 }));
    stone.position.set(x, (h + 0.4) / 2, z); stone.castShadow = true; stone.receiveShadow = true;
    worldGroup.add(stone);
    const c = document.createElement('canvas'); c.width = 128; c.height = 512;
    const g = c.getContext('2d');
    g.clearRect(0, 0, 128, 512);
    for (let i = 0; i < 60; i++) {
        const a = 0.08 + Math.random() * 0.3;
        g.strokeStyle = `rgba(235,248,252,${a})`;
        g.lineWidth = 1 + Math.random() * 2.4;
        const sx = Math.random() * 128;
        g.beginPath(); g.moveTo(sx, -20); g.lineTo(sx + (Math.random() * 8 - 4), 532); g.stroke();
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    const sheet = new THREE.Mesh(new THREE.PlaneGeometry(w, h),
        new THREE.MeshBasicMaterial({ map: tex, transparent: true, opacity: 0.75, depthWrite: false, side: THREE.DoubleSide }));
    sheet.position.set(x, h / 2 + 0.16, z + 0.29);
    worldGroup.add(sheet);
    animatedTex.push(dt => { tex.offset.y -= dt * 0.55; });
    const foam = new THREE.Mesh(new THREE.PlaneGeometry(w + 0.5, 0.8),
        new THREE.MeshBasicMaterial({ map: WASH_TEX, transparent: true, opacity: 0.5, blending: THREE.AdditiveBlending, depthWrite: false }));
    foam.rotation.x = -Math.PI / 2; foam.position.set(x, 0.17, z + 0.62);
    worldGroup.add(foam);
}

/* ---- Exterior: museum plaza under an open sky ---- */
(function plaza() {
    // sky dome (day gradient + sun + soft clouds), bright regardless of tone mapping
    const sky = canvasTexture(1024, 512, (g, w, h) => {
        const grad = g.createLinearGradient(0, 0, 0, h);
        grad.addColorStop(0, '#6ea8dd'); grad.addColorStop(0.55, '#a8c9e8'); grad.addColorStop(0.8, '#dce9f2'); grad.addColorStop(1, '#e8eef2');
        g.fillStyle = grad; g.fillRect(0, 0, w, h);
        g.fillStyle = 'rgba(255,250,235,0.95)'; g.shadowColor = '#fff7dd'; g.shadowBlur = 60;
        g.beginPath(); g.arc(w * 0.68, h * 0.3, 26, 0, Math.PI * 2); g.fill();
        g.shadowBlur = 40; g.fillStyle = 'rgba(255,255,255,0.5)';
        for (const [cx, cy, r] of [[w * 0.2, h * 0.36, 42], [w * 0.31, h * 0.33, 30], [w * 0.83, h * 0.46, 36], [w * 0.5, h * 0.24, 26]]) {
            g.beginPath(); g.ellipse(cx, cy, r * 1.9, r * 0.55, 0, 0, Math.PI * 2); g.fill();
        }
        g.shadowBlur = 0;
    });
    const dome = new THREE.Mesh(new THREE.SphereGeometry(75, 24, 16),
        new THREE.MeshBasicMaterial({ map: sky, side: THREE.BackSide, toneMapped: false }));
    dome.position.set(0, 0, 26);
    dome.userData.shared = true;
    worldGroup.add(dome);
    (window.__sky = window.__sky || []).push(dome);
    // distant city skyline behind the plaza hedge
    const skyline = canvasTexture(2048, 400, (g, w, h) => {
        g.clearRect(0, 0, w, h);
        for (const [tone, base] of [['rgba(150,175,196,0.85)', 0.42], ['rgba(112,138,160,0.9)', 0.6]]) {
            g.fillStyle = tone;
            let x = 0;
            while (x < w) {
                const bw = 60 + Math.random() * 130, bh = h * (base - Math.random() * 0.22);
                g.fillRect(x, h - bh, bw, bh);
                if (Math.random() < 0.4) g.fillRect(x + bw * 0.3, h - bh - 14, bw * 0.28, 14);
                x += bw + 6 + Math.random() * 18;
            }
        }
        g.fillStyle = 'rgba(58,74,44,0.95)';
        for (let x = 0; x < w; x += 26) {
            const r = 12 + Math.random() * 16;
            g.beginPath(); g.arc(x, h - 8, r, Math.PI, 0); g.fill();
        }
    });
    const far = new THREE.Mesh(new THREE.PlaneGeometry(64, 12.5),
        new THREE.MeshBasicMaterial({ map: skyline, transparent: true, toneMapped: false }));
    far.position.set(0, 6.05, 43.5); far.rotation.y = Math.PI;
    far.userData.shared = true;
    worldGroup.add(far);
    (window.__sky = window.__sky || []).push(far);

    // paving
    floorRect(-16, 16, 26, 42, new THREE.MeshStandardMaterial({
        ...pbr('concrete', { repeat: [6, 3] }), color: 0x9fa3a0, roughness: 0.95, envMapIntensity: 0.25 }));
    // perimeter hedges (and their invisible rails)
    const hedgeMat = new THREE.MeshStandardMaterial({ color: 0x30462e, roughness: 1 });
    for (const [hx0, hx1, hz0, hz1] of [[-16.3, -15.7, 26, 42], [15.7, 16.3, 26, 42], [-16.3, 16.3, 41.7, 42.3]]) {
        const hg = new THREE.Mesh(new THREE.BoxGeometry(hx1 - hx0, 1.05, hz1 - hz0), hedgeMat);
        hg.position.set((hx0 + hx1) / 2, 0.52, (hz0 + hz1) / 2); hg.castShadow = true;
        worldGroup.add(hg);
        addCollider(hx0, hx1, hz0, hz1);
    }
    ficusTree(-9, 34, 4.4, 0.7); ficusTree(9, 34, 4.4, 0.7);
    bench(-5, 33.5, 0); bench(5, 33.5, 0);
    planterBed(-14.6, -11.2, 27, 29.4, 5); planterBed(11.2, 14.6, 27, 29.4, 5);

    // facade: dark slate flanks + timber-mullioned glass curtain + open doors
    const slate = new THREE.MeshStandardMaterial({ ...pbr('concrete', { repeat: [2, 3] }), color: 0x3a3d44, envMapIntensity: 0.3 });
    box(5, 10.5, 0.5, slate, -10.5, 5.25, 26, { collide: true });
    box(5, 10.5, 0.5, slate, 10.5, 5.25, 26, { collide: true });
    // glass curtain x[-8,8] with a 2.6m open door bay at centre
    const curtain = new THREE.Mesh(new THREE.PlaneGeometry(16, 10.5), MAT.glass);
    curtain.position.set(0, 5.25, 26); worldGroup.add(curtain);
    addCollider(-8, -1.3, 25.85, 26.15);
    addCollider(1.3, 8, 25.85, 26.15);
    for (let i = 0; i <= 10; i++) {
        const mx = -8 + i * 1.6;
        if (Math.abs(mx) < 1.5) continue;
        box(0.13, 10.5, 0.22, TIMBER, mx, 5.25, 26, {});
    }
    box(16, 0.16, 0.22, TIMBER, 0, 3.6, 26, {});
    box(16, 0.16, 0.22, TIMBER, 0, 7.4, 26, {});
    // door leaves standing open + steel frame
    for (const s of [-1, 1]) {
        box(0.09, 3.4, 0.12, MAT.frameBlack, s * 1.32, 1.7, 26, {});
        const leaf = new THREE.Mesh(new THREE.PlaneGeometry(1.25, 3.3), MAT.glass);
        leaf.position.set(s * 1.9, 1.68, 26.55); leaf.rotation.y = s * 1.15;
        worldGroup.add(leaf);
    }
    // museum name across the fascia band + crimson banners on the flanks
    box(16.2, 1.15, 0.24, new THREE.MeshStandardMaterial({ color: 0x232529, roughness: 0.7 }), 0, 8.15, 26.14, {});
    goldLettering('The Museum of Political Imprisonment', 0, 8.15, 26.31, new THREE.Vector3(0, 0, 1), 2.6);
    const bannerTex = canvasTexture(256, 768, (g, w, h) => {
        g.fillStyle = '#98002e'; g.fillRect(0, 0, w, h);
        g.fillStyle = 'rgba(244,241,234,.96)'; g.textAlign = 'center';
        g.font = '700 118px Georgia, serif';
        ['N', 'P', 'P', 'C'].forEach((ch, i) => g.fillText(ch, w / 2, 190 + i * 150));
    });
    for (const s of [-1, 1]) {
        const b = new THREE.Mesh(new THREE.PlaneGeometry(1.5, 4.5),
            new THREE.MeshStandardMaterial({ map: bannerTex, roughness: 0.85, emissive: 0xffffff, emissiveMap: bannerTex, emissiveIntensity: 0.18 }));
        b.position.set(s * 10.5, 5.6, 26.32);
        worldGroup.add(b);
    }
    rooms.push({
        name: 'Museum Plaza', minX: -16, maxX: 16, minZ: 26, maxZ: 42,
        rig: { key: { p: [0, 13, 33], t: [0, 0, 33], i: 210, angle: 1.0, dist: 34, c: 0xf2f6ff },
            fills: [[-7, 4.5, 31, 40, 0xeaf2ff], [7, 4.5, 31, 40, 0xeaf2ff], [0, 5, 27.5, 34, 0xfff4e2]] } });
})();

/* ---- Grand atrium: x[-13,13] z[6,26], 10.5m tall ---- */
(function grandAtrium() {
    const Y = 10.5;
    // warm travertine floor
    floorRect(-13, 13, 6, 26, new THREE.MeshStandardMaterial({
        ...pbr('marble', { repeat: [7, 5] }), color: 0xe8ddca, roughness: 0.42, envMapIntensity: 0.5 }));
    cofferedCeiling(-13, 13, 6, 26, Y, { beam: TIMBER, ceil: MAT.ceiling, bay: 5, skylight: true });
    wallRun('z', -13, 6, 26, Y, { doors: [{ at: 8.5, w: 2.4, h: 3.0 }] });   // → Garden of Remembrance
    wallRun('z', 13, 6, 26, Y, { doors: [{ at: 24, w: 2.8, h: 3.1 }] });    // → Museum Shop
    wallRun('x', 6, -13, 13, Y, { doors: [{ at: 0, w: 3.2, h: 3.2 }] });    // → Hall of Figures

    // timber slat wainscot on the tall side walls (skip the shop doorway)
    for (const sx of [-12.82, 12.82]) {
        for (let z = 7.2; z <= 25; z += 0.44) {
            if (sx > 0 && z > 22.35 && z < 25.65) continue;
            const slat = new THREE.Mesh(new THREE.BoxGeometry(0.09, 4.1, 0.16), TIMBER);
            slat.position.set(sx, 2.2, z);
            worldGroup.add(slat);
        }
    }
    goldLettering('Museum Shop', 12.83, 3.72, 24, new THREE.Vector3(-1, 0, 0), 0.42);
    // tall timber columns
    for (const cx of [-6.5, 6.5]) for (const cz of [9.5, 14.5, 19.5, 23.8]) column(cx, cz, Y, 0.42, TIMBER);

    // ---- reflecting pond + waterfall + sculpture axis ----
    const refl = new Reflector(new THREE.PlaneGeometry(6.6, 5.4), {
        textureWidth: 512, textureHeight: 512, color: 0x8fa8a4, clipBias: 0.003,
    });
    refl.rotation.x = -Math.PI / 2; refl.position.set(0, 0.14, 15.5);
    worldGroup.add(refl); window.__reflector = refl;
    const aqua = new THREE.Mesh(new THREE.PlaneGeometry(6.6, 5.4),
        new THREE.MeshStandardMaterial({ color: 0x9fd4cc, transparent: true, opacity: 0.16, roughness: 0.08, envMapIntensity: 1.1, depthWrite: false }));
    aqua.rotation.x = -Math.PI / 2; aqua.position.set(0, 0.155, 15.5);
    worldGroup.add(aqua);
    // stone curb
    for (const [wS, dS, xS, zS] of [[7.6, 0.5, 0, 12.45], [7.6, 0.5, 0, 18.55], [0.5, 5.6, -3.55, 15.5], [0.5, 5.6, 3.55, 15.5]]) {
        const curb = new THREE.Mesh(new THREE.BoxGeometry(wS, 0.36, dS), MAT.plinth);
        curb.position.set(xS, 0.18, zS); curb.castShadow = true; curb.receiveShadow = true;
        worldGroup.add(curb);
        addCollider(xS - wS / 2, xS + wS / 2, zS - dS / 2, zS + dS / 2);
    }
    waterfall(0, 12.05, 2.2, 2.7);
    brokenChain(0, 21.5);
    anchors.sculpt = [2.4, Y - 0.9, 23.2];
    anchors.sculptTarget = [0, 1.3, 21.5];

    // hanging light installation over the pond (cascading emissive rods)
    for (let i = 0; i < 60; i++) {
        const len = 0.7 + Math.random() * 1.9;
        const bottom = 5.2 + Math.random() * 2.8;
        const rod = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, len, 6),
            new THREE.MeshBasicMaterial({ color: Math.random() < 0.6 ? 0xcdeee8 : 0xfff4de, toneMapped: false }));
        rod.position.set(-3.6 + Math.random() * 7.2, bottom + len / 2, 11.8 + Math.random() * 6.8);
        worldGroup.add(rod);
        const wire = new THREE.Mesh(new THREE.CylinderGeometry(0.004, 0.004, Y - (bottom + len), 4),
            new THREE.MeshStandardMaterial({ color: 0x2a2a2a }));
        wire.position.set(rod.position.x, bottom + len + (Y - bottom - len) / 2, rod.position.z);
        worldGroup.add(wire);
    }

    // greenery: big ficus pair + planter beds hugging the stair block
    ficusTree(-5.4, 19.8, 4.6, 0.62); ficusTree(5.4, 19.8, 4.6, 0.62);
    ficusTree(11.4, 8.4, 3.8, 0.55);
    planterBed(-10.15, -8.4, 12, 20, 6);
    planterBed(8.6, 9.9, 12.5, 18.5, 4);

    // ---- monumental stair (west) up to the mezzanine ----
    const stone = new THREE.MeshStandardMaterial({ ...pbr('marble', { repeat: [1.6, 1.6] }), color: 0xded3c0, roughness: 0.5, envMapIntensity: 0.4 });
    grandStair(-12.8, -10.4, 22, 11, 4.6, stone);
    addCollider(-10.45, -10.3, 11, 22);                                 // solid stair flank
    addCollider(-12.8, -10.4, 10.85, 11.05, -Infinity, 4.4);            // under-run face (pass above only)

    // ---- mezzanine: north band + east strip (walkable, y=4.6) ----
    // self-lit so the mezzanine underside doesn't read as a black band from the
    // atrium floor (a downward face gets no direct light)
    const fascia = new THREE.MeshStandardMaterial({ color: 0xf1efe9, roughness: 0.85, emissive: 0xf3ecdf, emissiveIntensity: 0.3 });
    box(26, 0.3, 5, fascia, 0, 4.45, 8.5, {});
    box(3, 0.3, 11, fascia, 11.5, 4.45, 16.5, {});
    floorRect(-13, 13, 6, 11, MAT.galleryFloor, 4.61);
    floorRect(10, 13, 11, 22, MAT.galleryFloor, 4.61);
    addFloorZone(-13, 13, 6, 11, 4.6);
    addFloorZone(10, 13, 11, 22, 4.6);
    balustrade('x', 11, -10.34, 10, 4.6);
    balustrade('z', 10, 11, 22, 4.6);
    // (east strip's south rail is omitted here — the Level-2 south balcony
    //  continues off it and carries the overlook rail instead)
    // mezzanine planter (west; the east half of the band is the café)
    for (const px of [-8]) {
        const pb = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.4, 0.8), MAT.benchWood);
        pb.position.set(px, 4.8, 7.2); worldGroup.add(pb);
        bushSprite(px - 0.3, 4.98, 7.2, 0.7); bushSprite(px + 0.35, 4.98, 7.2, 0.6);
        addCollider(px - 0.8, px + 0.8, 6.8, 7.6, 4.4, 6.2);
    }
    // upstairs hang: faces over the balustrade (west half) + the Debs quote
    goldLettering('"While there is a soul in prison, I am not free."', -3.5, 9.15, 6.18, new THREE.Vector3(0, 0, 1), 1.9);
    goldLettering('— Eugene V. Debs, 1918', 2, 8.05, 6.18, new THREE.Vector3(0, 0, 1), 0.7);
    (DATA.faces || []).slice(6, 10).forEach((f, i) => {
        hangArt(f, new THREE.Vector3(-11 + i * 3, 6.15, 6.18), new THREE.Vector3(0, 0, 1), { frame: MAT.frameBlack, artH: 1.05, gallery: 'Faces of the Database' });
    });
    (DATA.faces || []).slice(10, 13).forEach((f, i) => {
        hangArt(f, new THREE.Vector3(12.82, 6.15, 13 + i * 3.4), new THREE.Vector3(-1, 0, 0), { frame: MAT.frameBlack, artH: 1.0, gallery: 'Faces of the Database' });
    });

    // ---- reception desk + interpretive panels + standees ----
    box(3.4, 1.0, 0.9, MAT.benchWood, 5, 0.5, 22.5, {});
    addCollider(3.25, 6.75, 22.0, 23.0, 0, 2.8);        // banded (Level-2 balcony passes above)
    box(3.6, 0.08, 1.05, new THREE.MeshStandardMaterial({ color: 0xf4f1ea, roughness: 0.4 }), 5, 1.06, 22.5, {});
    goldLettering('Welcome', 5, 0.62, 22.94, new THREE.Vector3(0, 0, 1), 0.55);
    wallPanel(titleTexture(), new THREE.Vector3(0, 5.6, 6.14), new THREE.Vector3(0, 0, 1), 8.2, 3.2, { emissive: 0.42 });
    wallPanel(panelTexture('Welcome', 'A museum of\nAmerican dissent',
        'Everyone in this building was jailed, exiled, or detained in the United States for political reasons — for organizing a union, refusing a draft, demanding a vote, preaching a faith, or imagining their nation free. Ahead lies the Hall of Figures and, opening off it, a gallery for each movement. Beyond are the archive, a reading room, a theater, and a replica cell. Click any work to inspect it.'),
        new THREE.Vector3(12.7, 2.15, 20.5), new THREE.Vector3(-1, 0, 0), 2.3, 3.0,
        { interact: { kind: 'panel', n: 'A museum of American dissent', l1: 'Welcome', d: 'Everyone in this building was jailed, exiled, or detained in the United States for political reasons. Ahead lies the Hall of Figures and a gallery for each movement. Click any work to inspect it and follow it to the full record.', u: '/database' } });
    wallPanel(panelTexture('The Collection', 'One database,\nthousands of lives',
        `The National Political Prisoner Coalition documents ${DATA.stats.total || 'thousands of'} political prisoners across ${DATA.stats.eras || 'dozens of'} eras of American history — ${DATA.stats.inCustody || 'many'} of them still in custody today. The works hung here are drawn live from that database: every frame links to a full case record you can read, cite, and act on.`),
        new THREE.Vector3(12.7, 2.15, 15.5), new THREE.Vector3(-1, 0, 0), 2.3, 3.0,
        { interact: { kind: 'panel', n: 'One database, thousands of lives', l1: 'The Collection', d: 'Every frame in this museum links to a full record in the NPPC database.', u: '/database' } });

    const st = DATA.standees.slice(0, 4);
    // these sit under the mezzanine/Level-2 balcony — band their colliders to
    // the ground level so they don't wall off the walkway above
    const GB = [0, 2.8];
    [[-8.6, 23.4, 0.55], [-3.6, 24.6, -0.35], [-6.4, 9.2, 0.45], [6.6, 9.2, -0.45]]
        .forEach((s, i) => { if (st[i]) standee(st[i], s[0], s[1], s[2], GB); });
    bench(-4.2, 23.6, 0, false, GB); bench(0, 10.2, Math.PI, false, GB);

    rooms.push({
        name: 'Grand Atrium', minX: -13, maxX: 13, minZ: 6, maxZ: 26,
        rig: { key: { p: [0, Y - 0.7, 16], t: [0, 0.2, 15.5], i: 230, angle: 0.85, dist: 30 },
            fills: [[0, 6.4, 22, 42, 0xfff0dc], [-8, 5.8, 11, 30, 0xfff3e4], [7, 6.6, 8.5, 26, 0xffe7c8], [0, 6.8, 8.5, 26, 0xffeede]] } });
})();

/* ---- Café / social lounge on the atrium mezzanine (east half of the north
   band, y=4.6), overlooking the hall over the balustrade — a green living
   wall, cascading plants, paper lanterns, a coffee bar and bar seating.
   Colliders are height-banded so the ground floor below stays clear. ---- */
(function cafe() {
    const FY = 4.6, CY = 10.5;                       // mezzanine floor / atrium ceiling
    const band = (x0, x1, z0, z1) => addCollider(x0, x1, z0, z1, 4.4, 6.6);
    const woodMat = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1.4, 0.6] }), color: 0x6a4d31, roughness: 0.6, envMapIntensity: 0.5 });
    const stoneTop = new THREE.MeshStandardMaterial({ color: 0xece7db, roughness: 0.4, envMapIntensity: 0.6 });
    const greenMat = new THREE.MeshStandardMaterial({ color: 0x2b5238, roughness: 0.95, envMapIntensity: 0.12 });
    const chrome = new THREE.MeshStandardMaterial({ color: 0xcdd0d3, metalness: 0.9, roughness: 0.28, envMapIntensity: 1.1 });
    const leafMat = new THREE.MeshStandardMaterial({ color: 0x3f6a37, roughness: 0.7, side: THREE.DoubleSide, envMapIntensity: 0.4 });

    // living / trellis wall against the north wall
    const gw = new THREE.Mesh(new THREE.BoxGeometry(11, 3.6, 0.12), greenMat);
    gw.position.set(6.5, FY + 1.9, 6.4); gw.receiveShadow = true; worldGroup.add(gw);
    for (let i = 0; i < 12; i++) {                    // brass trellis battens
        const bat = new THREE.Mesh(new THREE.BoxGeometry(0.04, 3.5, 0.03), MAT.brass);
        bat.position.set(1.4 + i * 0.92, FY + 1.9, 6.34); worldGroup.add(bat);
    }
    goldLettering('Café', 6.5, FY + 3.15, 6.33, new THREE.Vector3(0, 0, 1), 0.55);

    // cascading hanging plant: strands of little leaves draping down
    function hangingPlant(x, z, topY, len) {
        const g = new THREE.Group(); g.position.set(x, topY, z); worldGroup.add(g);
        for (let s = 0; s < 5; s++) {
            const a = (s / 5) * Math.PI * 2, rx = Math.cos(a) * 0.12, rz = Math.sin(a) * 0.12;
            const wire = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.006, len, 4),
                new THREE.MeshStandardMaterial({ color: 0x395c2e }));
            wire.position.set(rx, -len / 2, rz); g.add(wire);
            for (let k = 0; k < 5; k++) {
                const lf = new THREE.Mesh(new THREE.PlaneGeometry(0.12, 0.2), leafMat);
                lf.position.set(rx + (Math.random() - 0.5) * 0.05, -0.2 - k * (len / 5), rz);
                lf.rotation.set(0.7, s + k, 0); g.add(lf);
            }
        }
    }
    for (const [px, pz, ln] of [[2.4, 6.7, 1.6], [4.4, 6.7, 2.0], [6.5, 6.7, 1.5], [8.6, 6.7, 2.1], [10.6, 6.7, 1.7]])
        hangingPlant(px, pz, FY + 3.5, ln);

    // back-bar shelves with coffee bags + mugs
    for (const sy of [FY + 1.5, FY + 2.1]) {
        const sh = new THREE.Mesh(new THREE.BoxGeometry(7, 0.05, 0.28), woodMat);
        sh.position.set(6, sy, 6.62); sh.castShadow = true; worldGroup.add(sh);
        for (let i = 0; i < 12; i++) {
            const bag = new THREE.Mesh(new THREE.BoxGeometry(0.16, 0.24, 0.12),
                new THREE.MeshStandardMaterial({ color: [0x8a5a2b, 0x2f4f4f, 0x7a2f3d, 0x3a3f4a][i % 4], roughness: 0.7 }));
            bag.position.set(3 + i * 0.55, sy + 0.15, 6.62); bag.castShadow = true; worldGroup.add(bag);
        }
    }

    // coffee bar counter
    const bar = new THREE.Mesh(new THREE.BoxGeometry(7.4, 1.05, 0.7), woodMat);
    bar.position.set(6, FY + 0.525, 7.5); bar.castShadow = true; bar.receiveShadow = true; worldGroup.add(bar);
    const top = new THREE.Mesh(new THREE.BoxGeometry(7.7, 0.07, 0.86), stoneTop);
    top.position.set(6, FY + 1.09, 7.5); worldGroup.add(top);
    band(2.2, 9.8, 7.05, 7.95);
    // espresso machine + grinder + cup stack on the bar
    const em = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.42, 0.5), chrome);
    em.position.set(5, FY + 1.33, 7.5); em.castShadow = true; worldGroup.add(em);
    for (const gx of [4.8, 5.2]) {
        const grp = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 0.12, 8), chrome);
        grp.position.set(gx, FY + 1.16, 7.68); worldGroup.add(grp);
    }
    const grinder = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.36, 0.2), matteBlackAtrium());
    grinder.position.set(5.9, FY + 1.3, 7.5); worldGroup.add(grinder);
    for (let i = 0; i < 3; i++) {
        const cups = new THREE.Mesh(new THREE.CylinderGeometry(0.055, 0.045, 0.18, 12),
            new THREE.MeshStandardMaterial({ color: 0xf2efe8, roughness: 0.5 }));
        cups.position.set(6.7 + i * 0.18, FY + 1.22, 7.5); worldGroup.add(cups);
    }
    // pastry display case at the end of the bar
    const disp = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.35, 0.55), MAT.glass);
    disp.position.set(8.7, FY + 1.31, 7.5); worldGroup.add(disp);

    // bar stool
    function stool(x, z, ry) {
        const g = new THREE.Group(); g.position.set(x, FY, z); g.rotation.y = ry; worldGroup.add(g);
        const seat = new THREE.Mesh(new THREE.CylinderGeometry(0.17, 0.17, 0.06, 16), woodMat);
        seat.position.y = 0.66; seat.castShadow = true; g.add(seat);
        const post = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.66, 10), chrome);
        post.position.y = 0.33; g.add(post);
        const ring = new THREE.Mesh(new THREE.TorusGeometry(0.13, 0.012, 6, 16), chrome);
        ring.position.y = 0.24; ring.rotation.x = Math.PI / 2; g.add(ring);
        const foot = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.18, 0.02, 16), chrome);
        foot.position.y = 0.01; g.add(foot);
    }
    for (let i = 0; i < 6; i++) stool(3.2 + i * 1.1, 8.35, Math.PI);   // facing the bar
    // a rail of stools facing OUT over the balustrade (the overlook bar)
    const ledge = new THREE.Mesh(new THREE.BoxGeometry(8, 0.06, 0.36), stoneTop);
    ledge.position.set(5.5, FY + 1.06, 10.55); worldGroup.add(ledge);
    band(1.5, 9.6, 10.35, 10.75);
    for (let i = 0; i < 5; i++) stool(2.4 + i * 1.5, 10.05, 0);        // facing the atrium view

    // two small café tables with chairs
    function cafeTable(x, z) {
        const g = new THREE.Group(); g.position.set(x, FY, z); worldGroup.add(g);
        const t = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.42, 0.05, 20), stoneTop);
        t.position.y = 0.74; t.castShadow = true; g.add(t);
        const ped = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.09, 0.74, 10), chrome);
        ped.position.y = 0.37; g.add(ped);
        for (const a of [0, Math.PI]) {
            const ch = new THREE.Group(); ch.position.set(Math.sin(a) * 0.62, 0, Math.cos(a) * 0.62); ch.rotation.y = a; g.add(ch);
            const cs = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.2, 0.05, 16), woodMat); cs.position.y = 0.46; cs.castShadow = true; ch.add(cs);
            const cp = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 0.46, 8), chrome); cp.position.y = 0.23; ch.add(cp);
            const bk = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.4, 0.04), woodMat); bk.position.set(0, 0.68, -0.18); ch.add(bk);
        }
        band(x - 0.9, x + 0.9, z - 0.9, z + 0.9);
    }
    cafeTable(3.4, 9.2); cafeTable(7.6, 9.3);

    // paper-lantern pendants over the café
    function lantern(x, z, y, r) {
        const cord = new THREE.Mesh(new THREE.CylinderGeometry(0.004, 0.004, CY - y, 4),
            new THREE.MeshStandardMaterial({ color: 0x2a2a2a }));
        cord.position.set(x, (CY + y) / 2, z); worldGroup.add(cord);
        const lamp = new THREE.Mesh(new THREE.SphereGeometry(r, 18, 12),
            new THREE.MeshStandardMaterial({ color: 0xfff6ea, emissive: 0xffe7c0, emissiveIntensity: 0.75, roughness: 0.6 }));
        lamp.scale.y = 0.86; lamp.position.set(x, y, z); worldGroup.add(lamp);
    }
    for (const [lx, lz, ly, lr] of [[3, 8.5, 8.3, 0.26], [5.2, 9.3, 7.8, 0.32], [7.4, 8.6, 8.4, 0.24], [9.4, 9.4, 7.9, 0.3], [6.2, 7.6, 8.6, 0.22]])
        lantern(lx, lz, ly, lr);

    function matteBlackAtrium() { return new THREE.MeshStandardMaterial({ color: 0x24262b, roughness: 0.5 }); }
})();

/* ---- Level Two: an upper gallery balcony wrapping the atrium. Continues off
   the existing east-strip mezzanine with a new south balcony over the
   entrance (city view through the glass facade), overlooking the pond hall
   below. Reached by the grand stair; floor at y=4.6, colliders height-banded
   so the ground floor stays clear. ---- */
(function levelTwo() {
    const FY = 4.6, Y = 10.5;
    // soffit reads self-lit so the balcony underside isn't a black band from
    // the atrium floor (its downward face gets no direct light)
    const fascia = new THREE.MeshStandardMaterial({ color: 0xf1efe9, roughness: 0.85, emissive: 0xf3ecdf, emissiveIntensity: 0.32 });

    // --- south balcony slab (over the entrance), continuous off the east strip ---
    box(24.2, 0.32, 4, fascia, 1, FY - 0.15, 23.85, {});          // slab edge / soffit
    floorRect(-11, 13, 22, 25.8, MAT.galleryFloor, 4.61);
    addFloorZone(-11, 13, 22, 25.8, 4.6);
    // recessed downlights on the soffit underside (lit-balcony look + reads over
    // the entrance below)
    for (const [dx, dz] of [[-8, 23], [-3, 24.5], [3, 23], [8, 24.5], [11, 22.5]]) {
        const dl = new THREE.Mesh(new THREE.CircleGeometry(0.18, 18),
            new THREE.MeshBasicMaterial({ color: 0xfff1d6, toneMapped: false }));
        dl.rotation.x = Math.PI / 2; dl.position.set(dx, FY - 0.31, dz); worldGroup.add(dl);
    }
    // overlook rails: north edge (over the atrium) west of the east-strip
    // junction, and the west edge
    balustrade('x', 22, -11, 10, 4.6);
    balustrade('z', -11, 22, 25.8, 4.6);

    // --- upper gallery fit-out ---
    // art along the east wall of the east strip + the balcony's east wall
    (DATA.faces || []).slice(13, 18).forEach((f, k) => {
        hangArt(f, new THREE.Vector3(12.82, FY + 1.5, 12.5 + k * 2.4), new THREE.Vector3(-1, 0, 0),
            { frame: MAT.frameBlack, artH: 1.1, gallery: 'Level Two' });
    });
    (DATA.faces || []).slice(18, 21).forEach((f, k) => {
        hangArt(f, new THREE.Vector3(-9 + k * 4, FY + 1.5, 25.6), new THREE.Vector3(0, 0, -1),
            { frame: MAT.frameBlack, artH: 1.1, gallery: 'Level Two' });
    });
    trackLightZ(12, 21, 12.2, Y - 0.2, 5);

    // a couple of freestanding vitrines up on the balcony (built at y=0, lifted
    // to the mezzanine floor; colliders banded to the upper level so they
    // don't block the ground floor below)
    const UB = [4.3, 6.4];
    const lift = (g) => { if (g) g.position.y = FY; return g; };
    const arch2 = (DATA.archive || []);
    if (arch2.length) {
        lift(vitrine(arch2[3 % arch2.length], -6, 23.4, 0.2, UB));
        lift(vitrine(arch2[4 % arch2.length], 3, 23.4, -0.2, UB));
    }
    // reading benches looking out over the atrium
    lift(bench(6, 22.7, Math.PI, true, UB));
    lift(bench(-2, 22.7, Math.PI, true, UB));

    // down-lights so the upper level reads (banded emissive pucks under ceiling)
    for (const [px, pz] of [[-6, 23.6], [2, 23.6], [8, 23.6], [11.5, 15], [11.5, 19]]) {
        const puck = new THREE.Mesh(new THREE.CircleGeometry(0.22, 20),
            new THREE.MeshBasicMaterial({ color: 0xfff2d8, toneMapped: false }));
        puck.rotation.x = Math.PI / 2; puck.position.set(px, 6.4, pz); worldGroup.add(puck);
    }

    // signage visible from the hall below (part of the Grand Atrium — the
    // room system is 2D, so the upper level is lit by the atrium rig plus the
    // picture-lights on each frame, like the existing mezzanine)
    goldLettering('Level Two — Upper Gallery', 6, FY + 2.9, 22.06, new THREE.Vector3(0, 0, -1), 0.5);
})();

/* ---- Museum Shop: a retail wing off the atrium's east side.
   Real products from the store — pick one up (like a library book) and the
   action bar offers Buy, which opens the product's store page. ---- */
(function museumShop() {
    const SHOP = (DATA.shop || []);
    const X0 = 13, X1 = 24, Z0 = 16.5, Z1 = 26, Y = 4.2;
    const shelfWood = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1.2, 0.7] }), color: 0x9a7550, roughness: 0.55, envMapIntensity: 0.7 });
    const cream = new THREE.MeshStandardMaterial({ color: 0xf4f1ea, roughness: 0.85 });

    floorRect(X0, X1, Z0, Z1, new THREE.MeshStandardMaterial({
        ...pbr('woodfloor', { repeat: [4, 3.4], ao: true }), color: 0xd6c1a2, roughness: 0.9, envMapIntensity: 0.55 }));
    ceilRect(X0, X1, Z0, Z1, Y);
    wallRun('z', X1, Z0, Z1, Y, {});
    wallRun('x', Z0, X0, X1, Y, {});
    wallRun('x', Z1, X0, X1, Y, {});
    coveLight(X0 + 0.45, X1 - 0.45, Z0 + 0.45, Z1 - 0.45, Y - 0.22, 0xffe9c8);
    ceilingLight(18.5, Y, 21.2, 2.2, 0.24);
    trackLight(15, 22.5, Z1 - 1.1, Y - 0.05, 5);
    trackLight(15, 22.5, Z0 + 1.1, Y - 0.05, 4);
    trackLightZ(18, 24.5, X1 - 1.1, Y - 0.05, 4);

    goldLettering('Museum Shop', 13.17, 3.3, 19.5, new THREE.Vector3(1, 0, 0), 0.6);
    goldLettering('every purchase supports the coalition', 13.17, 2.55, 19.5, new THREE.Vector3(1, 0, 0), 0.4);

    /* ---- 3D product props -------------------------------------------------
       Every product is a physical object — a shirt on a wire hanger, a bound
       book, a die-cut sticker, a ceramic mug, a rolled poster tube — with the
       product photo printed on it. An invisible proxy box makes the whole
       prop clickable; picking it up lifts the entire prop off its rack. */
    const metalThin = new THREE.MeshStandardMaterial({ color: 0x9a9a98, metalness: 0.9, roughness: 0.35, envMapIntensity: 1.1 });
    const paper = new THREE.MeshStandardMaterial({ color: 0xf1ede0, roughness: 0.85 });
    const pagesMat = new THREE.MeshStandardMaterial({ color: 0xefe9da, roughness: 0.9 });
    const shirtColors = [0xe8e4dc, 0x2b2d33, 0x6d7683, 0x5c2231, 0x2e3d55, 0x4a4f3a, 0x8a8578];
    const printMat = () => new THREE.MeshStandardMaterial({ color: 0xd9d5cc, roughness: 0.55, emissive: 0xffffff, emissiveIntensity: 0.05 });
    const photoOn = (mat, { circle = false } = {}) => (tex) => {
        let final = tex;
        if (circle) {                                   // die-cut sticker crop
            const im = tex.image, s = Math.min(im.width, im.height);
            const c = document.createElement('canvas'); c.width = c.height = 256;
            const gc = c.getContext('2d');
            gc.beginPath(); gc.arc(128, 128, 124, 0, 7); gc.clip();
            gc.drawImage(im, (im.width - s) / 2, (im.height - s) / 2, s, s, 0, 0, 256, 256);
            gc.lineWidth = 14; gc.strokeStyle = '#f2efe6'; gc.stroke();
            final = new THREE.CanvasTexture(c);
        }
        final.colorSpace = THREE.SRGBColorSpace; final.anisotropy = 8;
        mat.map = final; mat.emissiveMap = final;
        mat.color.set(0xffffff); mat.needsUpdate = true;
    };
    const stream = (d, g, apply) => { if (d.img) artQueue.push({ url: d.img, pos: g.position.clone(), apply }); };
    function registerProp(g, d, pw, ph, pt) {
        const proxy = new THREE.Mesh(new THREE.BoxGeometry(pw, ph, pt),
            new THREE.MeshBasicMaterial({ transparent: true, opacity: 0, depthWrite: false }));
        g.add(proxy);
        worldGroup.add(g);
        const holdD = Math.min(1.55, Math.max(0.55, Math.max(pw, ph) * 0.95 + 0.2));
        interactables.push({ mesh: proxy, grab: g, data: { kind: 'product', gallery: 'Museum Shop', coverAxis: [0, 0, 1], holdD, ...d } });
        return g;
    }
    function bar(g, from, to, r, mat) {
        const dir = new THREE.Vector3().subVectors(to, from);
        const len = dir.length();
        const m = new THREE.Mesh(new THREE.CylinderGeometry(r, r, len, 8), mat);
        m.position.copy(from).addScaledVector(dir, 0.5);
        m.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), dir.normalize());
        g.add(m);
        return m;
    }
    function hangerInto(g, neckY, shoulderX, shoulderY) {
        const apex = new THREE.Vector3(0, neckY + 0.1, 0);
        bar(g, apex, new THREE.Vector3(shoulderX, shoulderY, 0), 0.007, metalThin);
        bar(g, apex, new THREE.Vector3(-shoulderX, shoulderY, 0), 0.007, metalThin);
        bar(g, new THREE.Vector3(shoulderX, shoulderY, 0), new THREE.Vector3(-shoulderX, shoulderY, 0), 0.006, metalThin);
        bar(g, apex, new THREE.Vector3(0, neckY + 0.17, 0), 0.008, metalThin);
        const hook = new THREE.Mesh(new THREE.TorusGeometry(0.042, 0.008, 8, 12, Math.PI), metalThin);
        hook.position.set(0, neckY + 0.17, 0); g.add(hook);
    }

    // T-shirt / hoodie on a wire hanger; photo printed on the chest
    function shirtProp(d, x, y, z, ry, idx) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const fabricM = new THREE.MeshStandardMaterial({ color: shirtColors[idx % shirtColors.length], roughness: 0.95, envMapIntensity: 0.15 });
        const s = new THREE.Shape();
        s.moveTo(-0.1, 0.35); s.lineTo(-0.3, 0.3); s.lineTo(-0.43, 0.09);
        s.lineTo(-0.335, 0.005); s.lineTo(-0.255, 0.115); s.lineTo(-0.235, -0.395);
        s.lineTo(0.235, -0.395); s.lineTo(0.255, 0.115); s.lineTo(0.335, 0.005);
        s.lineTo(0.43, 0.09); s.lineTo(0.3, 0.3); s.lineTo(0.1, 0.35);
        s.quadraticCurveTo(0, 0.25, -0.1, 0.35);
        const geo = new THREE.ExtrudeGeometry(s, { depth: 0.045, bevelEnabled: true, bevelThickness: 0.012, bevelSize: 0.012, bevelSegments: 2, curveSegments: 6 });
        geo.translate(0, 0, -0.028);
        const shirt = new THREE.Mesh(geo, fabricM);
        shirt.castShadow = true; shirt.receiveShadow = true; g.add(shirt);
        if (/hood/i.test(d.n)) {                        // hood lump + drawstrings
            const hood = new THREE.Mesh(new THREE.SphereGeometry(0.13, 10, 8), fabricM);
            hood.scale.set(1, 0.62, 0.5); hood.position.set(0, 0.315, -0.02); g.add(hood);
            for (const sx of [-0.045, 0.045]) bar(g, new THREE.Vector3(sx, 0.24, 0.055), new THREE.Vector3(sx, 0.1, 0.062), 0.005, new THREE.MeshStandardMaterial({ color: 0xd8d4ca, roughness: 1 }));
        }
        const pm = printMat();
        const print = new THREE.Mesh(new THREE.PlaneGeometry(0.27, 0.29), pm);
        print.position.set(0, 0.02, 0.062); g.add(print);
        stream(d, g, photoOn(pm));
        hangerInto(g, 0.35, 0.3, 0.3);
        return registerProp(g, d, 0.82, 0.98, 0.15);
    }

    // canvas tote hung by its handles
    function toteProp(d, x, y, z, ry) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const canvasM = new THREE.MeshStandardMaterial({ color: 0xe6dfc9, roughness: 0.95, envMapIntensity: 0.15 });
        const body = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.4, 0.055), canvasM);
        body.castShadow = true; body.receiveShadow = true; g.add(body);
        for (const hz of [-0.02, 0.02]) {
            const handle = new THREE.Mesh(new THREE.TorusGeometry(0.105, 0.011, 8, 14, Math.PI), canvasM);
            handle.position.set(0, 0.2, hz); g.add(handle);
        }
        const pm = printMat();
        const print = new THREE.Mesh(new THREE.PlaneGeometry(0.24, 0.24), pm);
        print.position.set(0, 0.01, 0.032); g.add(print);
        stream(d, g, photoOn(pm));
        return registerProp(g, d, 0.44, 0.72, 0.12);
    }

    // bound book standing face-out (cover, spine, page block)
    function bookProp(d, x, y, z, ry, idx, { w = 0.42, h = 0.58, t = 0.065 } = {}) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const tint = [0x3a3f4a, 0x5c2231, 0x2e4638, 0x54452c][idx % 4];
        const coverM = printMat(); coverM.color.set(0xcfccc2);
        const clothM = new THREE.MeshStandardMaterial({ color: tint, roughness: 0.6 });
        const book = new THREE.Mesh(new THREE.BoxGeometry(w, h, t),
            [pagesMat, clothM, pagesMat, pagesMat, coverM, clothM]);
        book.castShadow = true; book.receiveShadow = true; g.add(book);
        stream(d, g, photoOn(coverM));
        return registerProp(g, d, w + 0.05, h + 0.05, t + 0.04);
    }

    // ceramic mug with a handle and a wrapped print
    function mugProp(d, x, y, z, ry) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const ceramic = new THREE.MeshStandardMaterial({ color: 0xf4f2ec, roughness: 0.22, envMapIntensity: 1.1 });
        const bodyM = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.046, 0.105, 18),
            [ceramic, new THREE.MeshStandardMaterial({ color: 0x2e2620, roughness: 0.4 }), ceramic]);
        bodyM.castShadow = true; g.add(bodyM);
        const handle = new THREE.Mesh(new THREE.TorusGeometry(0.032, 0.0085, 8, 14, Math.PI), ceramic);
        handle.position.set(0.05, 0, 0); handle.rotation.z = -Math.PI / 2; g.add(handle);
        const pm = printMat();
        const label = new THREE.Mesh(new THREE.CylinderGeometry(0.052, 0.052, 0.082, 16, 1, true, -0.95, 1.9), pm);
        g.add(label);
        stream(d, g, photoOn(pm));
        return registerProp(g, d, 0.15, 0.13, 0.13);
    }

    // die-cut sticker / enamel pin leaning on a stand
    function stickerProp(d, x, y, z, ry, { pin = false } = {}) {
        const g = new THREE.Group(); g.position.set(x, y, z);
        g.rotation.y = ry; g.rotation.x = -0.3;
        const r = pin ? 0.055 : 0.1;
        const pm = printMat();
        const disc = new THREE.Mesh(new THREE.CylinderGeometry(r, r, pin ? 0.01 : 0.005, 24),
            [new THREE.MeshStandardMaterial({ color: pin ? 0xb8b6b0 : 0xffffff, metalness: pin ? 0.8 : 0, roughness: pin ? 0.3 : 0.6 }), pm, paper]);
        disc.rotation.x = Math.PI / 2;
        disc.castShadow = true; g.add(disc);
        stream(d, g, photoOn(pm, { circle: true }));
        return registerProp(g, d, r * 2.3, r * 2.3, 0.06);
    }

    // framed poster flat on the wall
    function posterFlatProp(d, x, y, z) {
        const g = new THREE.Group(); g.position.set(x, y, z);
        const frame = new THREE.Mesh(new THREE.BoxGeometry(1.0, 1.36, 0.03), MAT.frameBlack);
        frame.castShadow = true; g.add(frame);
        const pm = printMat();
        const sheet = new THREE.Mesh(new THREE.PlaneGeometry(0.92, 1.28), pm);
        sheet.position.z = 0.017; g.add(sheet);
        stream(d, g, photoOn(pm));
        return registerProp(g, d, 1.0, 1.36, 0.08);
    }

    // rolled poster tube standing in the browse crate
    function posterTubeProp(d, x, y, z, tiltX, tiltZ) {
        const g = new THREE.Group(); g.position.set(x, y, z);
        g.rotation.x = tiltX; g.rotation.z = tiltZ;
        const tube = new THREE.Mesh(new THREE.CylinderGeometry(0.034, 0.034, 0.92, 14), paper);
        tube.castShadow = true; g.add(tube);
        const pm = printMat();
        const wrap = new THREE.Mesh(new THREE.CylinderGeometry(0.0355, 0.0355, 0.5, 14, 1, true, -1.2, 2.4), pm);
        g.add(wrap);
        const core = new THREE.Mesh(new THREE.CircleGeometry(0.024, 12), new THREE.MeshStandardMaterial({ color: 0xd9d2bf, roughness: 1 }));
        core.rotation.x = -Math.PI / 2; core.position.y = 0.461; g.add(core);
        stream(d, g, photoOn(pm));
        return registerProp(g, d, 0.1, 0.96, 0.1);
    }

    // baseball cap resting on a surface
    function capProp(d, x, y, z, ry) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const fabricM = new THREE.MeshStandardMaterial({ color: 0x2e3d55, roughness: 0.95 });
        const dome = new THREE.Mesh(new THREE.SphereGeometry(0.095, 14, 8, 0, Math.PI * 2, 0, Math.PI / 2), fabricM);
        dome.scale.set(1, 0.82, 1); dome.castShadow = true; g.add(dome);
        const brim = new THREE.Mesh(new THREE.CylinderGeometry(0.085, 0.085, 0.012, 14, 1, false, -Math.PI / 2.6, Math.PI / 1.3), fabricM);
        brim.position.set(0, 0.004, 0.09); brim.scale.set(1, 1, 1.35); g.add(brim);
        const pm = printMat();
        const patch = new THREE.Mesh(new THREE.CircleGeometry(0.038, 16), pm);
        patch.position.set(0, 0.052, 0.088); patch.rotation.x = -0.5; g.add(patch);
        stream(d, g, photoOn(pm, { circle: true }));
        return registerProp(g, d, 0.22, 0.14, 0.26);
    }

    // boxed product (candles, sets, anything else) — photo on the carton front
    function boxProp(d, x, y, z, ry) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const carton = new THREE.MeshStandardMaterial({ color: 0xcdb794, roughness: 0.9 });
        const pm = printMat();
        const bx = new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.3, 0.12),
            [carton, carton, carton, carton, pm, carton]);
        bx.castShadow = true; bx.receiveShadow = true; g.add(bx);
        stream(d, g, photoOn(pm));
        return registerProp(g, d, 0.3, 0.34, 0.16);
    }

    /* ---- non-interactive retail dressing (fixtures & set decoration) ---- */
    const steelMat = new THREE.MeshStandardMaterial({ color: 0xb9bbbe, metalness: 0.85, roughness: 0.35, envMapIntensity: 1.0 });
    const glassMat = MAT.glass;
    const kraftMat = new THREE.MeshStandardMaterial({ color: 0xc9ad82, roughness: 0.9 });
    const matteBlack = new THREE.MeshStandardMaterial({ color: 0x1c1e22, roughness: 0.5 });

    // neat colour-blocked stack of folded shirts
    function foldedStack(x, y, z, n = 4, ry = 0) {
        for (let i = 0; i < n; i++) {
            const c = shirtColors[(i + (x | 0)) % shirtColors.length];
            const fold = new THREE.Mesh(new THREE.BoxGeometry(0.34, 0.055, 0.34),
                new THREE.MeshStandardMaterial({ color: c, roughness: 0.95, envMapIntensity: 0.12 }));
            fold.position.set(x, y + 0.03 + i * 0.06, z);
            fold.rotation.y = ry + (Math.random() - 0.5) * 0.05;
            fold.castShadow = true; fold.receiveShadow = true; worldGroup.add(fold);
        }
    }
    // pendant light with an emissive bulb
    function pendant(x, y, z, shade = 0x2a2c30) {
        const cord = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.006, y - 0.1, 5), matteBlack);
        cord.position.set(x, (y) / 2 + Y - y, z); // hang from ceiling
        const drop = Y - y;
        cord.position.set(x, Y - drop / 2, z); cord.scale.y = drop / (y - 0.1 || 1);
        worldGroup.add(cord);
        const cone = new THREE.Mesh(new THREE.ConeGeometry(0.16, 0.2, 20, 1, true),
            new THREE.MeshStandardMaterial({ color: shade, metalness: 0.6, roughness: 0.4, side: THREE.DoubleSide }));
        cone.position.set(x, y, z); cone.castShadow = true; worldGroup.add(cone);
        const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.05, 12, 8),
            new THREE.MeshBasicMaterial({ color: 0xfff2d4, toneMapped: false }));
        bulb.position.set(x, y - 0.06, z); worldGroup.add(bulb);
    }
    // hanging blade sign over an aisle
    function bladeSign(text, x, y, z, normal) {
        for (const dx of [-0.55, 0.55]) {
            const ch = new THREE.Mesh(new THREE.CylinderGeometry(0.004, 0.004, Y - y - 0.1, 4), steelMat);
            ch.position.set(x + dx, (Y + y + 0.1) / 2, z); ch.scale.y = 1; worldGroup.add(ch);
        }
        const panel = new THREE.Mesh(new THREE.BoxGeometry(1.5, 0.42, 0.05), matteBlack);
        panel.position.set(x, y, z); panel.castShadow = true; worldGroup.add(panel);
        goldLettering(text, x, y, z + (normal.z > 0 ? 0.03 : -0.03), normal, 0.3);
    }
    // glass display case on a cabinet base; returns the top surface height
    function displayCase(cx, cz, w, d, ry) {
        const base = new THREE.Group(); base.position.set(cx, 0, cz); base.rotation.y = ry;
        const cab = new THREE.Mesh(new THREE.BoxGeometry(w, 0.92, d), shelfWood);
        cab.position.y = 0.46; cab.castShadow = true; cab.receiveShadow = true; base.add(cab);
        const kick = new THREE.Mesh(new THREE.BoxGeometry(w - 0.06, 0.08, d - 0.06), matteBlack);
        kick.position.y = 0.04; base.add(kick);
        // glass vitrine
        const gh = 0.34;
        for (const [px, pz, pw, pd] of [[0, d / 2, w, 0.02], [0, -d / 2, w, 0.02], [w / 2, 0, 0.02, d], [-w / 2, 0, 0.02, d]]) {
            const pane = new THREE.Mesh(new THREE.BoxGeometry(pw, gh, pd), glassMat);
            pane.position.set(px, 0.92 + gh / 2, pz); base.add(pane);
        }
        const lid = new THREE.Mesh(new THREE.BoxGeometry(w, 0.02, d), glassMat);
        lid.position.y = 0.92 + gh; base.add(lid);
        const shelf = new THREE.Mesh(new THREE.BoxGeometry(w - 0.1, 0.02, d - 0.1),
            new THREE.MeshStandardMaterial({ color: 0xe9e4d8, roughness: 0.8 }));
        shelf.position.y = 0.93; base.add(shelf);
        worldGroup.add(base);
        addCollider(cx - (Math.abs(Math.cos(ry)) * w + Math.abs(Math.sin(ry)) * d) / 2,
            cx + (Math.abs(Math.cos(ry)) * w + Math.abs(Math.sin(ry)) * d) / 2,
            cz - (Math.abs(Math.sin(ry)) * w + Math.abs(Math.cos(ry)) * d) / 2,
            cz + (Math.abs(Math.sin(ry)) * w + Math.abs(Math.cos(ry)) * d) / 2);
        return base;
    }
    // point-of-sale cluster: terminal, card reader, receipt printer, tip jar, bag stack
    function posCluster(x, y, z, ry) {
        const g = new THREE.Group(); g.position.set(x, y, z); g.rotation.y = ry;
        const term = new THREE.Mesh(new THREE.BoxGeometry(0.3, 0.22, 0.22), matteBlack);
        term.position.set(0, 0.11, 0); term.castShadow = true; g.add(term);
        const scr = new THREE.Mesh(new THREE.PlaneGeometry(0.24, 0.16),
            new THREE.MeshBasicMaterial({ color: 0x9fd0e8, toneMapped: false }));
        scr.position.set(0, 0.15, 0.112); scr.rotation.x = -0.25; g.add(scr);
        const reader = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.14, 0.05), matteBlack);
        reader.position.set(0.28, 0.07, 0.05); reader.rotation.x = -0.3; g.add(reader);
        const printer = new THREE.Mesh(new THREE.BoxGeometry(0.16, 0.12, 0.16),
            new THREE.MeshStandardMaterial({ color: 0xe8e6df, roughness: 0.6 }));
        printer.position.set(-0.34, 0.06, 0.02); g.add(printer);
        const jar = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.05, 0.14, 14), glassMat);
        jar.position.set(-0.34, 0.07, 0.32); g.add(jar);
        // stack of kraft bags
        const bags = new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.05, 0.34), kraftMat);
        bags.position.set(0.28, 0.03, 0.36); bags.castShadow = true; g.add(bags);
        worldGroup.add(g);
    }
    // stack of shopping baskets
    function basketStack(x, z, n = 4) {
        for (let i = 0; i < n; i++) {
            const bk = new THREE.Group(); bk.position.set(x, 0.12 + i * 0.11, z); bk.rotation.y = i * 0.12;
            const bt = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.02, 0.3), matteBlack); bk.add(bt);
            for (const [px, pz, pw, pd] of [[0, 0.15, 0.4, 0.02], [0, -0.15, 0.4, 0.02], [0.2, 0, 0.02, 0.3], [-0.2, 0, 0.02, 0.3]]) {
                const wall = new THREE.Mesh(new THREE.BoxGeometry(pw, 0.14, pd), matteBlack);
                wall.position.set(px, 0.08, pz); bk.add(wall);
            }
            bk.castShadow = true; worldGroup.add(bk);
        }
        addCollider(x - 0.25, x + 0.25, z - 0.2, z + 0.2);
    }
    // torso mannequin on a stand wearing a featured tee (streams the product art)
    function mannequin(d, x, z, ry) {
        const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry;
        const formMat = new THREE.MeshStandardMaterial({ color: 0xdedad0, roughness: 0.6, envMapIntensity: 0.4 });
        const baseP = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.2, 0.05, 20), steelMat);
        baseP.position.y = 0.025; g.add(baseP);
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.95, 10), steelMat);
        pole.position.y = 0.5; g.add(pole);
        const torso = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.12, 0.5, 20), formMat);
        torso.position.y = 1.2; torso.scale.z = 0.62; torso.castShadow = true; g.add(torso);
        const shoulders = new THREE.Mesh(new THREE.SphereGeometry(0.16, 16, 10), formMat);
        shoulders.position.y = 1.42; shoulders.scale.set(1, 0.5, 0.62); g.add(shoulders);
        const neck = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.06, 0.12, 12), formMat);
        neck.position.y = 1.52; g.add(neck);
        // a real tee over the form: soft shell + chest print
        const tee = new THREE.Mesh(new THREE.CylinderGeometry(0.17, 0.14, 0.42, 20), new THREE.MeshStandardMaterial({ color: 0x2b2d33, roughness: 0.95 }));
        tee.position.y = 1.2; tee.scale.z = 0.66; g.add(tee);
        const pm = printMat();
        const print = new THREE.Mesh(new THREE.PlaneGeometry(0.16, 0.18), pm);
        print.position.set(0, 1.24, 0.116); g.add(print);
        stream(d, g, photoOn(pm));
        worldGroup.add(g);
        interactables.push({ mesh: print, data: { kind: 'panel', gallery: 'Museum Shop', n: d.n, l1: d.l1, l2: 'Featured', d: d.d, u: d.u } });
        addCollider(x - 0.28, x + 0.28, z - 0.28, z + 0.28);
    }
    // slatwall panel with pegged small goods
    function slatwall(x0, x1, wy, z, normal, items) {
        const w = x1 - x0;
        const panel = new THREE.Mesh(new THREE.BoxGeometry(w, 1.5, 0.05),
            new THREE.MeshStandardMaterial({ color: 0xd8d2c4, roughness: 0.8 }));
        panel.position.set((x0 + x1) / 2, wy, z); panel.receiveShadow = true; worldGroup.add(panel);
        for (let i = 0; i < 8; i++) {
            const slat = new THREE.Mesh(new THREE.BoxGeometry(w - 0.06, 0.03, 0.02),
                new THREE.MeshStandardMaterial({ color: 0xbfb8a8, roughness: 0.7 }));
            slat.position.set((x0 + x1) / 2, wy - 0.7 + i * 0.18, z + normal.z * 0.03); worldGroup.add(slat);
        }
        (items || []).forEach((d, i) => {
            const col = i % 5, row = Math.floor(i / 5);
            const px = x0 + 0.4 + col * ((w - 0.8) / 4);
            const py = wy + 0.35 - row * 0.5;
            const peg = new THREE.Mesh(new THREE.CylinderGeometry(0.008, 0.008, 0.1, 6), steelMat);
            peg.rotation.x = Math.PI / 2; peg.position.set(px, py + 0.14, z + normal.z * 0.08); worldGroup.add(peg);
            stickerProp(d, px, py, z + normal.z * 0.12, normal.z > 0 ? 0 : Math.PI, { pin: isPin(d) });
        });
    }
    // rotating postcard/print spinner rack
    function spinnerRack(x, z, cards) {
        const g = new THREE.Group(); g.position.set(x, 0, z);
        const baseP = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.32, 0.06, 20), matteBlack);
        baseP.position.y = 0.03; g.add(baseP);
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 1.5, 12), steelMat);
        pole.position.y = 0.78; g.add(pole);
        (cards || []).forEach((d, i) => {
            const wing = new THREE.Group(); wing.rotation.y = (i / Math.max(1, cards.length)) * Math.PI * 2; g.add(wing);
            const mesh = new THREE.Mesh(new THREE.PlaneGeometry(0.02, 1.2), new THREE.MeshStandardMaterial({ color: 0xcfcabd, roughness: 0.7 }));
            mesh.position.set(0.02, 0.85, 0); wing.add(mesh);
            for (let r = 0; r < 3; r++) {
                const pm = printMat();
                const card = new THREE.Mesh(new THREE.PlaneGeometry(0.26, 0.34), pm);
                card.position.set(0.16, 0.55 + r * 0.38, 0.01); wing.add(card);
                stream(d, wing, (t) => { photoOn(pm)(t); });
            }
        });
        g.castShadow = true; worldGroup.add(g);
        addCollider(x - 0.35, x + 0.35, z - 0.35, z + 0.35);
    }
    // entrance doormat
    function doormat(x, z) {
        const mat = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.03, 0.9),
            new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [3, 2] }), color: 0x4a3f33, roughness: 1, envMapIntensity: 0.1 }));
        mat.position.set(x, 0.015, z); mat.receiveShadow = true; worldGroup.add(mat);
    }

    // ---- sort products onto their fixtures ----
    const byType = t => SHOP.filter(p => p.type === t);
    const isTote = d => /tote|bag/i.test(d.n);
    const isMug = d => /mug|cup|bottle|tumbler/i.test(d.n);
    const isCap = d => /\bhat\b|\bcap\b|beanie/i.test(d.n);
    const isPin = d => /pin|button|badge/i.test(d.n);
    const apparel = byType('apparel').slice(0, 7);
    const books = byType('book').slice(0, 6);
    const posters = byType('poster').slice(0, 6);
    const small = byType('small').slice(0, 8);
    const taken = new Set([...apparel, ...books, ...posters, ...small]);
    const misc = SHOP.filter(p => !taken.has(p)).slice(0, 8);
    const mugs = misc.filter(isMug).slice(0, 3);
    const caps = misc.filter(d => !mugs.includes(d) && isCap(d)).slice(0, 2);
    const boxed = misc.filter(d => !mugs.includes(d) && !caps.includes(d));

    /* ---- dense instanced "stock": rows of the same product filling shelves,
       the way a real shop looks. One draw call each for all mugs / books /
       boxes. Kept as non-interactive dressing around the hero pickable props. */
    const SHOP_C = new THREE.Vector3(18.5, 0, 21);
    const STOCK = { mugs: [], books: [], boxes: [] };
    const _col = new THREE.Color();
    const _e = new THREE.Euler(), _v = new THREE.Vector3(), _q2 = new THREE.Quaternion(), _one = new THREE.Vector3(1, 1, 1);
    const mugColors = [0xffffff, 0x2f5c96, 0x2b8a86, 0xb23a48, 0xe6b93f, 0x39527a, 0xf2efe8, 0x6d7683];
    const bookColors = [0x3a4a63, 0x7a2f3d, 0x2e5140, 0xb2762f, 0x4a3a63, 0xc9c3b4, 0x2b3138, 0x8a8578];
    const boxColors = [0xd8cbb0, 0xbcc7cf, 0xcdb794, 0xc7b6c9];
    function localMat(x, y, z, ry, sx, sy, sz) {
        return new THREE.Matrix4().compose(_v.set(x - SHOP_C.x, y, z - SHOP_C.z),
            _q2.setFromEuler(_e.set(0, ry, 0)), new THREE.Vector3(sx, sy, sz));
    }
    const pushMug = (x, y, z, ry, c) => STOCK.mugs.push({ m: localMat(x, y + 0.055, z, ry, 1, 1, 1), c });
    const pushBook = (x, y, z, h, c) => STOCK.books.push({ m: localMat(x, y + h / 2, z, 0, 0.14, h, 0.04), c });
    const pushBox = (x, y, z, c) => STOCK.boxes.push({ m: localMat(x, y + 0.1, z, 0, 0.17, 0.2, 0.24), c });

    // fill one shelf face with a dense row appropriate to its height
    function fillShelf(fx, y, cz, length, tier, seed = 0) {
        const type = tier < 0.62 ? 'book' : tier < 1.12 ? 'mug' : tier < 1.62 ? 'book' : 'box';
        if (type === 'mug') {
            const n = Math.floor(length / 0.15);
            for (let i = 0; i < n; i++) pushMug(fx, y, cz - length / 2 + 0.09 + i * (length / n), 0, mugColors[(i + seed) % mugColors.length]);
        } else if (type === 'book') {
            const n = Math.floor(length / 0.05);
            for (let i = 0; i < n; i++) pushBook(fx, y, cz - length / 2 + 0.05 + i * (length / n), 0.19 + (i % 5) * 0.015, bookColors[(i + seed) % bookColors.length]);
        } else {
            const n = Math.floor(length / 0.28);
            for (let i = 0; i < n; i++) pushBox(fx, y, cz - length / 2 + 0.16 + i * (length / n), boxColors[(i + seed) % boxColors.length]);
        }
    }

    // freestanding double-sided gondola running along z: a thin center spine
    // with shelves cantilevering out both faces, packed with stock
    function gondola(cx, cz, len, label) {
        const half = 0.45, topY = 1.98;
        box(0.09, topY, len, shelfWood, cx, topY / 2, cz, {});          // center spine
        box(half * 2, 0.12, len, matteBlack, cx, 0.06, cz, {});         // base kick
        box(half * 2 - 0.06, 0.3, len * 0.84, matteBlack, cx, topY + 0.18, cz, {}); // header board
        if (label) goldLettering(label, cx, topY + 0.18, cz + 0.01, new THREE.Vector3(0, 0, 1), 0.24);
        for (const side of [1, -1]) {
            for (const ty of [0.44, 0.94, 1.44, 1.92]) {
                box(0.4, 0.04, len - 0.08, shelfWood, cx + side * 0.25, ty, cz, { shadow: false });
                fillShelf(cx + side * 0.32, ty + 0.02, cz, len - 0.24, ty, (side > 0 ? 0 : 3) + (cx | 0));
            }
        }
        addCollider(cx - half - 0.05, cx + half + 0.05, cz - len / 2 - 0.05, cz + len / 2 + 0.05);
    }

    // wall-mounted shelf run of stock along z at the given wall x
    function wallShelves(wx, faceSign, cz, len) {
        for (const ty of [0.62, 1.12, 1.62, 2.12]) {
            box(0.3, 0.04, len, shelfWood, wx + faceSign * 0.15, ty, cz, { shadow: false });
            const br = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.24, 0.03), matteBlack);
            br.position.set(wx + faceSign * 0.14, ty - 0.14, cz - len / 2 + 0.2); worldGroup.add(br);
            fillShelf(wx + faceSign * 0.13, ty + 0.02, cz, len - 0.3, ty, (ty * 7 | 0));
        }
    }

    // strip fluorescent fixture on the ceiling
    function stripLight(x, z, len) {
        const h = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.09, len), new THREE.MeshStandardMaterial({ color: 0xe8e8e6, roughness: 0.5, metalness: 0.2 }));
        h.position.set(x, Y - 0.05, z); worldGroup.add(h);
        const t = new THREE.Mesh(new THREE.BoxGeometry(0.17, 0.04, len - 0.12), new THREE.MeshBasicMaterial({ color: 0xfff6e4, toneMapped: false }));
        t.position.set(x, Y - 0.1, z); worldGroup.add(t);
    }

    // ---- fixtures & merchandising ----
    doormat(14.0, 24.0);
    for (const [sx, sz, sl] of [[15.4, 21, 7.4], [18.5, 21, 7.4], [21.6, 21, 7.4]]) stripLight(sx, sz, sl);

    // two packed gondolas down the middle
    gondola(18.0, 20.6, 5.4, 'Gifts');
    gondola(21.0, 20.6, 5.4, 'Books & Mugs');
    // east wall shelving full of mugs & books
    wallShelves(23.72, -1, 21, 6.2);
    addCollider(23.55, 24, 17.7, 24.3);
    // north wall shelving too (short run east of the apparel rail)
    // (kept clear on the west for the rail)

    // ---- hero interactive products salted through the stock ----
    // apparel on a coat-hanger rail (north wall, west half)
    if (apparel.length) {
        const railY = 2.32, railZ = 25.5, x0 = 16.2 - (apparel.length - 1) * 0.47;
        const rail = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, apparel.length * 0.95 + 0.7, 10), MAT.brass);
        rail.rotation.z = Math.PI / 2; rail.position.set(16.2, railY, railZ); worldGroup.add(rail);
        apparel.forEach((d, i) => {
            const x = x0 + i * 0.95;
            const hook = new THREE.Mesh(new THREE.TorusGeometry(0.05, 0.012, 8, 14), MAT.metal);
            hook.position.set(x, railY + 0.02, railZ); worldGroup.add(hook);
            if (isTote(d)) toteProp(d, x, railY - 0.3, railZ + 0.02, Math.PI);
            else shirtProp(d, x, railY - 0.56, railZ + 0.02, Math.PI, i);
        });
        goldLettering('Apparel', 16.2, 3.3, 25.83, new THREE.Vector3(0, 0, -1), 0.42);
        addCollider(13.4, 19.2, 25.2, 26);
        // folded stacks on a low shelf under the rail
        for (let i = 0; i < 4; i++) foldedStack(14.3 + i * 0.5, 0.62, 25.2, 3 + (i % 2), 0);
        box(2.4, 0.6, 0.44, shelfWood, 15.05, 0.3, 25.25, { collide: true });
    }
    // hero mugs & books face-out on the east wall shelf front
    mugs.forEach((d, i) => mugProp(d, 23.5, 1.175, 19.4 + i * 1.4, -Math.PI / 2));
    books.slice(0, 4).forEach((d, i) => bookProp(d, 23.44, [0.86, 1.36][i % 2] + 0.02, 19.6 + Math.floor(i / 2) * 2.2, -Math.PI / 2, i, { w: 0.4, h: 0.54 }));

    // checkout: glass display case + POS + baskets, front-left by the entrance
    displayCase(14.7, 20.6, 0.95, 2.8, 0);
    posCluster(14.75, 1.28, 20.2, Math.PI / 2);
    basketStack(14.6, 23.0, 4);
    goldLettering('Checkout', 13.2, 2.7, 20.6, new THREE.Vector3(1, 0, 0), 0.34);
    // impulse stickers under the register glass
    if (small.length) small.slice(0, 4).forEach((d, i) => stickerProp(d, 14.4 + (i % 2) * 0.5, 0.99, 20.0 + Math.floor(i / 2) * 0.5, Math.PI / 2, { pin: isPin(d) }));

    // slatwall of pegged small goods on the south wall (west half)
    slatwall(14.0, 17.6, 1.5, 16.72, new THREE.Vector3(0, 0, 1), small.slice(2));
    goldLettering('Stickers & Pins', 15.8, 2.55, 16.7, new THREE.Vector3(0, 0, 1), 0.34);
    // framed prints on the south wall (east half)
    posters.slice(0, 2).forEach((d, i) => posterFlatProp(d, 19.6 + i * 2.2, 2.0, 16.72));
    if (posters.length) goldLettering('Prints', 21.8, 3.1, 16.7, new THREE.Vector3(0, 0, 1), 0.34);

    // two wire spinner racks of postcards near the entrance
    spinnerRack(16.6, 23.4, posters.length ? posters : SHOP.slice(0, 6));
    spinnerRack(16.6, 18.4, SHOP.slice(0, 6));

    // a mannequin bust wearing the featured tee, greeting you at the door
    if (apparel.length) mannequin(apparel[0], 14.5, 22.6, -0.7);

    // caps + boxed goods on a gondola end-cap table
    if (caps.length || boxed.length) {
        box(1.0, 0.9, 0.7, shelfWood, 18.0, 0.45, 17.6, { collide: true });
        box(1.1, 0.06, 0.8, cream, 18.0, 0.92, 17.6, {});
        caps.forEach((d, i) => capProp(d, 17.75 + i * 0.5, 0.96, 17.6, i ? 2.4 : -0.7));
        boxed.slice(0, 3).forEach((d, i) => boxProp(d, 20.7, 1.06, 18.6 + i * 0.0 - i * 0.0, Math.PI - 0.1));
    }

    // pendant lights over the counter and each gondola
    for (const [px, pz] of [[14.7, 20.6], [18.0, 20.6], [21.0, 20.6]]) pendant(px, 3.1, pz);

    // hanging blade sign over the entrance aisle
    bladeSign('Museum Shop', 15.0, 3.2, 22.0, new THREE.Vector3(-1, 0, 0));

    // interpretive panel linking straight to the store (north wall, east)
    wallPanel(panelTexture('Museum Shop', 'Take the work\nhome with you',
        'Everything in this room is real — shirts, books, prints, mugs, and stickers from the coalition\'s store. Pick any item up to look at it closely; if you want it, the Buy button opens its store page, and every purchase funds the documentation and defense work this museum is built on.'),
        new THREE.Vector3(20.5, 2.1, 25.78), new THREE.Vector3(0, 0, -1), 1.6, 2.2,
        { interact: { kind: 'panel', n: 'Take the work home with you', l1: 'Museum Shop', d: 'Everything in this room is a real product from the coalition\'s store. Every purchase funds the documentation and defense work this museum is built on.', u: '/store' } });

    // greenery + rug so it reads as a room, not a stockroom
    ficusTree(23.0, 24.6, 2.9, 0.42);
    const rug = new THREE.Mesh(new THREE.PlaneGeometry(2.6, 3.0),
        new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [2, 2] }), color: 0x7a2f3d, roughness: 1, envMapIntensity: 0.2 }));
    rug.rotation.x = -Math.PI / 2; rug.position.set(19.5, 0.02, 21.0); rug.receiveShadow = true; worldGroup.add(rug);

    // ---- flush the instanced stock ----
    const ceramicMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.26, metalness: 0.02, envMapIntensity: 1.0 });
    const bookStockMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.62, envMapIntensity: 0.4 });
    const boxStockMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.85, envMapIntensity: 0.3 });
    const mugBody = new THREE.CylinderGeometry(0.05, 0.045, 0.11, 14);
    const mugHandle = new THREE.TorusGeometry(0.03, 0.008, 6, 10, Math.PI);
    const mugBase = mergeBucket([
        { geo: mugBody, matrix: new THREE.Matrix4() },
        { geo: mugHandle, matrix: new THREE.Matrix4().compose(new THREE.Vector3(0.05, 0, 0), new THREE.Quaternion().setFromEuler(new THREE.Euler(0, 0, -Math.PI / 2)), _one) },
    ]);
    const unitBox = new THREE.BoxGeometry(1, 1, 1);
    const flush = (geo, mat, arr) => {
        if (!arr.length) return;
        const im = new THREE.InstancedMesh(geo, mat, arr.length);
        arr.forEach((s, i) => { im.setMatrixAt(i, s.m); im.setColorAt(i, _col.set(s.c)); });
        im.instanceMatrix.needsUpdate = true; if (im.instanceColor) im.instanceColor.needsUpdate = true;
        im.castShadow = false; im.receiveShadow = true; im.position.copy(SHOP_C);
        im.frustumCulled = false; worldGroup.add(im);
    };
    flush(mugBase, ceramicMat, STOCK.mugs);
    flush(unitBox, bookStockMat, STOCK.books);
    flush(unitBox, boxStockMat, STOCK.boxes);

    rooms.push({
        name: 'Museum Shop', minX: X0, maxX: X1, minZ: Z0, maxZ: Z1,
        rig: { key: { p: [18.5, Y - 0.5, 20.8], t: [18.5, 0.7, 21.2], i: 120, angle: 1.05, dist: 13 },
            fills: [[15.5, 3.1, 24.3, 20, 0xfff0dc], [22.3, 3.1, 18.3, 20, 0xfff3e4], [18.5, 3.3, 17.6, 16, 0xffeede], [14.6, 2.6, 18.7, 12, 0xfff3e4]] } });
})();

/* ---- Garden of Remembrance: a circular contemplation rotunda west of the
   atrium, lit by a broad oculus, ringed with portraits of those who died in
   custody, around a raked-gravel karesansui with granite boulders and a
   single courtyard tree. ---- */
(function rotunda() {
    const CX = -24, CZ = 8.5, R = 8.5, H = 7.2, OC = 3.4, Rg = 5.4;
    const doorHalf = 0.24;   // half-angle of the east opening (toward the atrium)

    // warm travertine floor ring + a soft ambient underfloor
    const floor = new THREE.Mesh(new THREE.CircleGeometry(R, 72), new THREE.MeshStandardMaterial({
        ...pbr('marble', { repeat: [5, 5] }), color: 0xe7dcc7, roughness: 0.42, envMapIntensity: 0.5 }));
    floor.rotation.x = -Math.PI / 2; floor.position.set(CX, 0.01, CZ); floor.receiveShadow = true; worldGroup.add(floor);

    // curved plaster wall with a gap for the doorway (opening at +x / east)
    const wallGeo = new THREE.CylinderGeometry(R, R, H, 96, 1, true, Math.PI / 2 + doorHalf, TAU - 2 * doorHalf);
    const wall = new THREE.Mesh(wallGeo, new THREE.MeshStandardMaterial({
        ...pbr('plaster', { repeat: [10, 3] }), color: 0xdcd7cc, side: THREE.BackSide, envMapIntensity: 0.22 }));
    wall.position.set(CX, H / 2, CZ); wall.receiveShadow = true; worldGroup.add(wall);
    // wall colliders: a ring of boxes approximating the cylinder, door arc left open
    for (let i = 0; i < 40; i++) {
        const a = (i / 40) * TAU, aa = Math.atan2(Math.sin(a), Math.cos(a));
        if (Math.abs(aa) < doorHalf + 0.14) continue;
        const wx = CX + Math.cos(a) * R, wz = CZ + Math.sin(a) * R, s = (TAU * R / 40) * 0.62;
        addCollider(wx - s, wx + s, wz - s, wz + s);
    }
    // skirting + a picture-rail band
    for (const [yy, hh, col] of [[0.12, 0.24, 0xbfb6a6], [3.3, 0.06, 0xcfc7b8]]) {
        const band = new THREE.Mesh(new THREE.CylinderGeometry(R - 0.02, R - 0.02, hh, 96, 1, true, Math.PI / 2 + doorHalf, TAU - 2 * doorHalf),
            new THREE.MeshStandardMaterial({ color: col, roughness: 0.7, side: THREE.BackSide }));
        band.position.set(CX, yy, CZ); worldGroup.add(band);
    }

    // flat ceiling ring with a luminous oculus in the middle
    const ceil = new THREE.Mesh(new THREE.RingGeometry(OC, R, 72, 1),
        new THREE.MeshStandardMaterial({ color: 0xe4ddcf, roughness: 0.9, side: THREE.DoubleSide }));
    ceil.rotation.x = Math.PI / 2; ceil.position.set(CX, H, CZ); worldGroup.add(ceil);
    // oculus well (short cylinder up to a bright disc) + emissive sky disc
    const well = new THREE.Mesh(new THREE.CylinderGeometry(OC, OC, 0.9, 48, 1, true),
        new THREE.MeshStandardMaterial({ color: 0xf3efe6, side: THREE.BackSide, roughness: 0.8 }));
    well.position.set(CX, H + 0.45, CZ); worldGroup.add(well);
    const sky = new THREE.Mesh(new THREE.CircleGeometry(OC, 48),
        new THREE.MeshBasicMaterial({ color: 0xfdf6e8, toneMapped: false }));
    sky.rotation.x = Math.PI / 2; sky.position.set(CX, H + 0.9, CZ); worldGroup.add(sky);
    const glow = new THREE.Mesh(new THREE.CircleGeometry(OC + 0.25, 48),
        new THREE.MeshBasicMaterial({ color: 0xfff3d8, transparent: true, opacity: 0.5, depthWrite: false }));
    glow.rotation.x = -Math.PI / 2; glow.position.set(CX, H - 0.02, CZ); worldGroup.add(glow);

    // ---- raked-gravel karesansui on a low stone platform ----
    const SAND_TEX = canvasTexture(1024, 1024, (g, w, h) => {
        g.fillStyle = '#e9e2d2'; g.fillRect(0, 0, w, h);
        const cx = w / 2, cy = h / 2;
        g.strokeStyle = 'rgba(120,110,90,0.32)'; g.lineWidth = 2.6;
        for (let r = 23; r < w * 0.74; r += 14) {
            g.beginPath();
            for (let a = 0; a <= TAU + 0.06; a += 0.045) {
                const rr = r + Math.sin(a * 6) * 1.8;
                const x = cx + Math.cos(a) * rr, y = cy + Math.sin(a) * rr;
                a === 0 ? g.moveTo(x, y) : g.lineTo(x, y);
            }
            g.stroke();
        }
        for (let i = 0; i < 6500; i++) {
            g.globalAlpha = 0.05 + Math.random() * 0.09;
            g.fillStyle = Math.random() < 0.5 ? '#ffffff' : '#b6ab92';
            g.fillRect(Math.random() * w, Math.random() * h, 1.6, 1.6);
        }
        g.globalAlpha = 1;
    });
    const platform = new THREE.Mesh(new THREE.CylinderGeometry(Rg + 0.4, Rg + 0.46, 0.34, 72), MAT.plinth);
    platform.position.set(CX, 0.17, CZ); platform.castShadow = true; platform.receiveShadow = true; worldGroup.add(platform);
    const curb = new THREE.Mesh(new THREE.TorusGeometry(Rg + 0.4, 0.07, 10, 80),
        new THREE.MeshStandardMaterial({ color: 0x8f8778, roughness: 0.7, envMapIntensity: 0.3 }));
    curb.rotation.x = Math.PI / 2; curb.position.set(CX, 0.35, CZ); worldGroup.add(curb);
    const sand = new THREE.Mesh(new THREE.CircleGeometry(Rg, 80),
        new THREE.MeshStandardMaterial({ map: SAND_TEX, color: 0xf3ecdd, roughness: 0.95, envMapIntensity: 0.15 }));
    sand.rotation.x = -Math.PI / 2; sand.position.set(CX, 0.345, CZ); sand.receiveShadow = true; worldGroup.add(sand);

    // boulder grouping (asymmetric, like a real dry garden) + moss pads
    const gy = 0.345;
    const rocks = [[-1.4, 0.4, 1.35, 0.3], [-0.2, -0.1, 0.95, 1.1], [1.1, 0.7, 1.1, 2.1],
        [0.6, -1.3, 0.8, 0.6], [2.6, -0.4, 1.2, 1.6], [-2.7, -1.1, 0.9, 2.7], [3.1, 1.5, 0.7, 0.2]];
    for (const [dx, dz, s, ry] of rocks) {
        boulder(CX + dx, CZ + dz, s, ry, { yBase: gy, collide: false });
        const moss = new THREE.Mesh(new THREE.CircleGeometry(s * 0.7, 20), MOSS_MAT);
        moss.rotation.x = -Math.PI / 2; moss.position.set(CX + dx, gy + 0.006, CZ + dz); worldGroup.add(moss);
    }
    // the single courtyard tree, set slightly back
    gardenTree(CX - 0.4, CZ + 2.6, 5.6);

    // block the raised garden (square collider is invisible under the round bed)
    addCollider(CX - (Rg + 0.35), CX + (Rg + 0.35), CZ - (Rg + 0.35), CZ + (Rg + 0.35));

    // ---- ring of memorial portraits on the wall ----
    const mem = (DATA.faces || []).concat(DATA.monoliths || []);
    const nPort = 11;
    let mi = 0;
    for (let i = 0; i < nPort; i++) {
        const a = (i + 0.5) / nPort * TAU;                 // evenly spaced, offset off the door
        if (Math.abs(Math.atan2(Math.sin(a), Math.cos(a))) < doorHalf + 0.18) continue;  // skip the doorway
        const item = mem[mi++ % Math.max(1, mem.length)];
        if (!item) continue;
        const px = CX + Math.cos(a) * (R - 0.14), pz = CZ + Math.sin(a) * (R - 0.14);
        hangArt(item, new THREE.Vector3(px, 2.0, pz), new THREE.Vector3(-Math.cos(a), 0, -Math.sin(a)),
            { frame: MAT.frameBlack, artH: 1.15, gallery: 'Garden of Remembrance' });
    }

    // dedication over the doorway, on the wall inside
    goldLettering('Garden of Remembrance', CX + R - 0.16, 3.05, CZ, new THREE.Vector3(-1, 0, 0), 0.34);
    wallPanel(panelTexture('In Memoriam', 'Those who did not\ncome home',
        'Some of the people in this museum died in prison — of age, of illness, of neglect, of the state\'s slow violence. This garden is for them. The raked stone is tended and retended; the tree keeps its own time. Sit a while.'),
        new THREE.Vector3(CX - R + 0.16, 2.0, CZ), new THREE.Vector3(1, 0, 0), 2.0, 2.6,
        { interact: { kind: 'panel', n: 'Those who did not come home', l1: 'In Memoriam', d: 'This garden is dedicated to the people documented in this museum who died in custody. Every frame in the building links to a full record you can read and act on.', u: '/memorial' } });

    // low benches to sit and contemplate — set on the north/south axis, clear
    // of the east doorway
    bench(CX, CZ + Rg + 1.5, Math.PI, true);
    bench(CX, CZ - Rg - 1.5, 0, true);

    // ---- the connecting vestibule from the atrium's west wall ----
    const vzN = CZ - 1.2, vzS = CZ + 1.2, vxW = CX + R, vxE = -13;
    floorRect(vxW, vxE, vzN, vzS, new THREE.MeshStandardMaterial({
        ...pbr('marble', { repeat: [2, 1] }), color: 0xe7dcc7, roughness: 0.45, envMapIntensity: 0.5 }));
    ceilRect(vxW, vxE, vzN, vzS, 3.1);
    wallRun('x', vzN, vxW, vxE, 3.1, {});
    wallRun('x', vzS, vxW, vxE, 3.1, {});
    ceilingLight((vxW + vxE) / 2, 3.1, CZ, 1.4, 0.3);

    rooms.push({
        name: 'Garden of Remembrance', minX: CX - R, maxX: -13.4, minZ: CZ - R, maxZ: CZ + R,
        rig: { key: { p: [CX, H - 0.6, CZ], t: [CX, 0.4, CZ], i: 300, angle: 1.15, dist: 22 },
            fills: [[CX, 5.2, CZ, 26, 0xfff2df], [CX - 4, 3.4, CZ + 3, 18, 0xfff4e6], [CX + 4, 3.4, CZ - 3, 18, 0xfff4e6], [CX, 2.4, CZ, 14, 0xffeede]] } });
})();

/* ---- Hall of Figures (central spine) + data-driven galleries ---- */
const GAL = (DATA.galleries || []);
const BAND = 16;
const bandHi = r => -r * BAND;
const bandLo = r => -(r + 1) * BAND;
const galRows = Math.max(1, Math.ceil(GAL.length / 2));
const spineEnd = bandLo(galRows - 1) - 2;
const galleryMeta = [];   // {sign, midX, zLo, zHi, doorZ} per gallery for the spine walls

GAL.forEach((g, i) => {
    const sign = (i % 2 === 0) ? 1 : -1;
    const row = Math.floor(i / 2);
    const zHi = bandHi(row), zLo = bandLo(row), doorZ = (zHi + zLo) / 2;
    galleryMeta.push({ i, g, sign, row, zHi, zLo, doorZ, midX: sign * 14 });
});

// Central-court loop: link the deepest gallery on each side to the far-end
// room beside it (reading room east / theater west), when they sit close
// enough to bridge — closing the two enfilade circuits into one perimeter
// loop around the spine+archive core. Degrades gracefully when the geometry
// leaves too large a gap (odd gallery counts on one side).
const FAR_NORTH = spineEnd - 2;                    // north wall z of the reading room
const loopLinks = {};                              // sign -> { i, zGal, linkX }
// Only the east side links (to the flat reading room); the west far room is
// the sunken theater, whose tiers don't take a level pass-through.
[1].forEach((s) => {
    const metas = galleryMeta.filter((m) => m.sign === s);
    if (!metas.length) return;
    const deepest = metas.reduce((a, b) => (b.zLo < a.zLo ? b : a));
    const zGal = deepest.zLo + 0.2;                // deepest gallery's south wall
    const gap = zGal - FAR_NORTH;
    if (gap > 0.5 && gap <= 6.5) loopLinks[s] = { i: deepest.i, zGal, linkX: s * 19 };
});
// build one link corridor (floor + side walls + colliders) between a gallery
// south wall and a far room north wall; doors are cut by the room builders
function buildLoopCorridor(sign) {
    const lk = loopLinks[sign];
    if (!lk) return;
    const Y = 3.2, half = 2.6, x0 = lk.linkX - half, x1 = lk.linkX + half;
    const zN = lk.zGal, zS = FAR_NORTH;            // gallery side (north) → far room (south)
    floorRect(x0, x1, zS, zN, MAT.galleryFloor);
    ceilRect(x0, x1, zS, zN, Y);
    wallRun('z', x0, zS, zN, Y, {});
    wallRun('z', x1, zS, zN, Y, {});
    ceilingLight(lk.linkX, Y, (zN + zS) / 2, 1.2, 0.24);
}

(function spine() {
    const Y = CEIL.spine, X = 7;
    floorRect(-X, X, spineEnd, 6, MAT.hallFloor);
    cofferedCeiling(-X, X, spineEnd, 6, Y, { beam: TIMBER, ceil: MAT.ceiling, bay: 4, skylight: true });

    const eastDoors = galleryMeta.filter(m => m.sign > 0).map(m => ({ at: m.doorZ, w: 2.4, h: 2.8 }));
    const westDoors = galleryMeta.filter(m => m.sign < 0).map(m => ({ at: m.doorZ, w: 2.4, h: 2.8 }));
    wallRun('z', X, spineEnd, 6, Y, { doors: eastDoors });
    wallRun('z', -X, spineEnd, 6, Y, { doors: westDoors });
    wallRun('x', spineEnd, -X, X, Y, { doors: [{ at: 0, w: 3, h: 3 }] });   // → archive

    // monoliths down the centre
    const mono = DATA.monoliths || [];
    let mz = -2.5;
    mono.forEach((m, k) => {
        const mx = (k % 2 === 0) ? -1.7 : 1.7;
        const ry = (k % 2 === 0) ? 0.32 : -0.32;
        monolith(m, mx, mz, ry, accentHex(GAL[k] ? GAL[k].accent : 'teal'));
        mz -= 4.4;
        if (mz < spineEnd + 3) mz = spineEnd + 3;
    });

    // photo-mosaic panels filling the wall gaps between gallery doors, both sides
    const mosaic = DATA.mosaic || [];
    let mi = 0;
    const per = 40;
    function fillSide(sign) {
        const doors = galleryMeta.filter(m => m.sign === sign).map(m => m.doorZ).sort((a, b) => b - a);
        const edges = [6, ...doors.flatMap(d => [d + 1.3, d - 1.3]), spineEnd].sort((a, b) => b - a);
        for (let s = 0; s < edges.length - 1; s += 2) {
            const zA = edges[s], zB = edges[s + 1];
            const gap = zA - zB;
            if (gap < 2.2) continue;
            const slice = mosaic.slice(mi, mi + per); mi = (mi + per) % Math.max(1, mosaic.length);
            mosaicWall(slice.length ? slice : mosaic.slice(0, per), sign * (X - 0.16), 2.3, (zA + zB) / 2, Math.min(gap - 0.4, 9), 3.4, new THREE.Vector3(-sign, 0, 0), '/database');
        }
    }
    fillSide(1); fillSide(-1);

    // interpretive intro on the vestibule + a couple of hanging banners over the hall
    wallPanel(panelTexture('The Hall of Figures', 'Stand among them',
        'Down the centre stand portraits of one leading figure from each movement gathered here. The walls hold hundreds more — every documented face in the collection. Turn through any doorway to enter that movement\'s gallery.'),
        new THREE.Vector3(0, 4.6, 5.9), new THREE.Vector3(0, 0, 1), 5.2, 1.9, { emissive: 0.3 });
    (DATA.faces || []).slice(0, 2).forEach((f, k) => {
        hangingBanner(f, k === 0 ? -3.2 : 3.2, Y - 0.1, -3 - k * 6, 2.0, 3.0, new THREE.Vector3(0, 0, 1), { emissive: 0.4 });
    });

    for (let z = -1; z >= spineEnd + 2; z -= 9) ceilingLight(0, Y, z, 2.4, 0.3);
    for (let z = -6; z >= spineEnd + 4; z -= 12) { bench(0, z, 0); }

    // freestanding artifacts along the hall edges: a bronze orator on a plinth
    // at the threshold, document vitrines set between the portraits
    plinth(-4.6, 3.2, 0.75, 0.42);
    plasterFigure(-4.6, 3.2, 0.5, { scale: 0.78, bronze: true });
    const arch = (DATA.archive || []);
    if (arch.length) {
        vitrine(arch[0], 4.6, -7, -0.5);
        if (arch[1]) vitrine(arch[1], -4.6, -19, 0.5);
        if (arch[2]) vitrine(arch[2], 4.6, -31, -0.5);
    }
    // two movement banners hung high down the hall
    (DATA.monoliths || []).slice(0, 2).forEach((m, k) => {
        hangingBanner(m, k === 0 ? 4.4 : -4.4, Y - 0.1, -12 - k * 14, 1.8, 3.4, new THREE.Vector3(k === 0 ? -1 : 1, 0, 0), { emissive: 0.42 });
    });

    rooms.push({
        name: 'The Hall of Figures', minX: -X, maxX: X, minZ: spineEnd, maxZ: 6,
        rig: { key: { p: [0, Y - 0.6, -8], t: [0, 0, -8], i: 90, angle: 0.9, dist: 26 },
            fills: [[0, 5.4, -6, 26, 0xffeede], [0, 5.4, -24, 26, 0xffeede], [0, 5.4, -42, 24, 0xffeede], [0, 5.0, spineEnd + 6, 22, 0xffeede]] } });
})();

/* ---- Themed galleries: big, open, skylit halls, one per curated group ---- */
galleryMeta.forEach(({ i, g, sign, zHi, zLo, doorZ }) => {
    const Y = CEIL.gallery;                     // 6m — tall and airy
    const innerX = sign * 7;
    const outerX = sign * 25;                   // 18m wide open hall
    const loX = Math.min(innerX, outerX), hiX = Math.max(innerX, outerX);
    const zA = zLo + 0.2, zB = zHi - 0.2;       // ~15.6m deep
    const cxMid = sign * 16;
    const dark = (i % 2 === 1);                 // alternate skylit white-cube / dark dramatic
    const accent = accentHex(g.accent);
    const wallMat = dark ? DARKWALL : MAT.wall;
    const frameMat = [MAT.frameWood, MAT.frameBlack, MAT.frameGilt][i % 3];

    floorRect(loX, hiX, zA, zB, MAT.galleryFloor);
    if (dark) {
        ceilRect(loX, hiX, zA, zB, Y, new THREE.MeshStandardMaterial({ color: 0x17181c, roughness: 0.95 }));
        coveLight(cxMid - 8, cxMid + 8, zA + 0.35, zA + 0.35, Y - 0.14, accent);
        coveLight(cxMid - 8, cxMid + 8, zB - 0.35, zB - 0.35, Y - 0.14, accent);
    } else {
        cofferedCeiling(loX, hiX, zA, zB, Y, { beam: TIMBER, ceil: MAT.ceiling, bay: 4, skylight: true });
    }
    wallRun('z', outerX, zA, zB, Y, { mat: wallMat });
    // Interconnection: cut a doorway in the end walls shared with the same-side
    // gallery in the next/previous row, so the branches read as one enfilade
    // circuit (walk gallery→gallery) instead of dead-end rooms off the spine.
    const DW = 2.4, DH = 3.0;
    const row = Math.floor(i / 2);
    // Stagger the enfilade doors between two x positions by shared-wall parity
    // so the aligned doorways don't form a straight sightline down the whole
    // wing (which would reveal a culled gallery / the sky through the chain).
    // 22 and 9 both clear the end-wall art (at x±13,±19) and the floor fins.
    const boundaryX = (bIdx) => sign * ((((bIdx % 2) + 2) % 2 === 0) ? 22 : 9);
    const hasNext = i + 2 < GAL.length;   // deeper same-side gallery (lower z, zA wall)
    const hasPrev = i - 2 >= 0;            // shallower same-side gallery (higher z, zB wall)
    const lk = loopLinks[sign];           // loop link out of the deepest gallery's south wall
    const southX = boundaryX(row), northX = boundaryX(row - 1);
    const southDoors = [];
    if (hasNext) southDoors.push({ at: southX, w: DW, h: DH });
    if (lk && lk.i === i) southDoors.push({ at: lk.linkX, w: 2.4, h: 2.8 });
    wallRun('x', zA, loX, hiX, Y, { mat: wallMat, doors: southDoors });
    wallRun('x', zB, loX, hiX, Y, { mat: wallMat, doors: hasPrev ? [{ at: northX, w: DW, h: DH }] : [] });
    // bridge floor across the ~0.4m gap between the two galleries' walls
    if (hasNext) floorRect(southX - DW / 2 - 0.25, southX + DW / 2 + 0.25, zA - 0.6, zA + 0.1, MAT.galleryFloor);
    if (hasPrev) floorRect(northX - DW / 2 - 0.25, northX + DW / 2 + 0.25, zB - 0.1, zB + 0.6, MAT.galleryFloor);

    // track lighting over the outer wall + the fins
    trackLightZ(zA + 2, zB - 2, outerX - sign * 1.4, Y, 6);
    trackLightZ(zA + 2, zB - 2, cxMid, Y, 6);

    // interpretive panel beside the door
    wallPanel(panelTexture(`Gallery ${i + 1}`, g.title, g.intro),
        new THREE.Vector3(innerX + sign * 0.16, 1.95, doorZ - 4.5), new THREE.Vector3(sign, 0, 0), 2.4, 3.0,
        { interact: { kind: 'panel', n: g.title, l1: `Gallery ${i + 1}`, d: g.intro } });

    fillOpenGallery(g.items || [], sign, innerX, outerX, cxMid, zA, zB, doorZ, frameMat, accent, g.title);
    bench(sign * 9.5, doorZ, 0, true);          // red bench near the entrance
    bench(cxMid + sign * 3, doorZ, 0, true);

    // a freestanding artifact standing in the open floor (objects, not only
    // framed photos — the museum-object look from the references)
    const artZ = doorZ + 5.4;
    const arch = (DATA.archive || []);
    if (arch.length && i % 2 === 0) {
        vitrine(arch[(i / 2 | 0) % arch.length], cxMid, artZ, sign > 0 ? -0.4 : 0.4);   // document vitrine
    } else {
        plinth(cxMid, artZ, 1.1, 0.4);                                                   // bronze bust on a plinth
        const bust = new THREE.Group(); bust.position.set(cxMid, 1.1, artZ); worldGroup.add(bust);
        const bmat = new THREE.MeshStandardMaterial({ color: 0x6d4f2a, metalness: 0.9, roughness: 0.4, envMapIntensity: 1.3 });
        const sh = new THREE.Mesh(new THREE.SphereGeometry(0.24, 18, 12), bmat); sh.scale.set(1, 0.5, 0.62); sh.position.y = 0.12; sh.castShadow = true; bust.add(sh);
        const nk = new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.09, 0.16, 12), bmat); nk.position.y = 0.28; bust.add(nk);
        const hd = new THREE.Mesh(new THREE.SphereGeometry(0.15, 18, 16), bmat); hd.scale.set(0.9, 1.05, 0.95); hd.position.y = 0.46; hd.castShadow = true; bust.add(hd);
    }

    rooms.push({
        name: g.title, minX: loX, maxX: hiX, minZ: zA, maxZ: zB,
        rig: { key: { p: [cxMid, Y - 0.6, doorZ], t: [cxMid, 0.5, doorZ], i: dark ? 175 : 210, angle: 1.0, dist: 22 },
            fills: dark
                ? [[outerX - sign * 2.5, 3.6, doorZ - 4, 30, 0xfff3e2], [outerX - sign * 2.5, 3.6, doorZ + 4, 30, 0xfff3e2], [cxMid, 3.4, doorZ, 20, accent]]
                : [[outerX - sign * 2.5, 4.2, doorZ - 4, 40, 0xfff6ee], [outerX - sign * 2.5, 4.2, doorZ + 4, 40, 0xfff6ee], [innerX + sign * 4, 4.0, doorZ, 30, 0xfff2e0]] } });
});

/* ---- Archive: continues the spine axis, x[-8,8] z[spineEnd-16, spineEnd] ---- */
const archHiZ = spineEnd, archLoZ = spineEnd - 16;
(function archive() {
    const Y = CEIL.archive;
    floorRect(-8, 8, archLoZ, archHiZ, MAT.galleryFloor);
    ceilRect(-8, 8, archLoZ, archHiZ, Y);
    // north wall (z=spineEnd) built by spine with the door; add flanks up to x=±8
    wallRun('x', archHiZ, -8, -3.5, Y, {});
    wallRun('x', archHiZ, 3.5, 8, Y, {});
    wallRun('x', archLoZ, -8, 8, Y, { doors: [{ at: 0, w: 1.2, h: 2.1 }] });    // → cell
    wallRun('z', -8, archLoZ, archHiZ, Y, { doors: [{ at: spineEnd - 8, w: 2.1, h: 2.6 }] });  // → cinema
    wallRun('z', 8, archLoZ, archHiZ, Y, { doors: [{ at: spineEnd - 8, w: 2.1, h: 2.6 }] });   // → reading

    wallPanel(panelTexture('The Archive', 'The paper trail',
        'Movements leave paper: strike bulletins, defense-committee pamphlets, prison letters, flyers demanding freedom for people whose names would otherwise be lost. These cases are digitized from the NPPC archive. Through the east door is the reading room, where the full publications can be read cover to cover; to the west, the theater.'),
        new THREE.Vector3(0, 1.9, archLoZ + 0.16), new THREE.Vector3(0, 0, 1), 2.4, 2.9,
        { interact: { kind: 'panel', n: 'The paper trail', l1: 'The Archive', d: 'Strike bulletins, pamphlets, prison letters, and flyers from the NPPC digital archive. Click any case to inspect the document, or read the full scans next door in the reading room.', u: '/archive' } });

    const docs = (DATA.archive || []).slice(0, 10);
    const cz = (archLoZ + archHiZ) / 2;
    const spots = [
        [-3.4, cz + 4, 0.3], [-1.1, cz + 4, 0], [1.1, cz + 4, 0], [3.4, cz + 4, -0.3],
        [-3.4, cz - 4, 2.85], [-1.1, cz - 4, Math.PI], [1.1, cz - 4, Math.PI], [3.4, cz - 4, -2.85],
        [-2.2, cz, Math.PI / 2], [2.2, cz, -Math.PI / 2],
    ];
    docs.forEach((d, k) => { if (spots[k]) vitrine(d, spots[k][0], spots[k][1], spots[k][2]); });
    ceilingLight(0, Y, cz + 4, 1.6, 0.24); ceilingLight(0, Y, cz - 4, 1.6, 0.24);
    rooms.push({
        name: 'The Archive', minX: -8, maxX: 8, minZ: archLoZ, maxZ: archHiZ,
        rig: { key: { p: [0, Y - 0.4, cz], t: [0, 0.9, cz], i: 115, angle: 0.9, dist: 13 },
            fills: [[-2.5, 2.9, cz + 4, 20, 0xffe9c8], [2.5, 2.9, cz - 4, 20, 0xffe9c8]] } });
})();

/* ---- Solitary cell: x[-4,4] z[archLoZ-8, archLoZ] ---- */
(function cell() {
    const Y = CEIL.cell, zHi = archLoZ, zLo = archLoZ - 8, cz = (zHi + zLo) / 2;
    wallPanel(panelTexture('Period Room', 'Solitary',
        'This room reproduces, at full scale, a segregation cell: roughly 2.4 by 3.4 metres, concrete on six sides, one bunk, one steel door with a food slot. On any given day some 120,000 people are held in restrictive housing in U.S. prisons and jails. Several people in this museum spent decades in rooms like this one.'),
        new THREE.Vector3(-4.4, 1.85, zHi - 1.2), new THREE.Vector3(1, 0, 0), 1.9, 2.5,
        { interact: { kind: 'panel', n: 'Solitary', l1: 'Period Room', d: 'A full-scale reproduction of a segregation cell: concrete on six sides, one bunk, one steel door with a food slot. Several people documented in this museum spent decades in rooms like this.' } });
    floorRect(-3.85, 3.85, zLo + 0.15, zHi - 0.15, MAT.concreteFloor);
    ceilRect(-3.85, 3.85, zLo + 0.15, zHi - 0.15, Y, MAT.concrete);
    wallRun('x', zLo, -4, 4, Y, { mat: MAT.concrete });
    wallRun('z', -4, zLo, zHi, Y, { mat: MAT.concrete });
    wallRun('z', 4, zLo, zHi, Y, { mat: MAT.concrete });
    const door = box(0.06, 2.05, 1.05, MAT.metal, 0, 1.06, zHi - 0.2, { ry: 0.6 });
    addCollider(-0.6, 0.6, zHi - 0.7, zHi + 0.1);
    box(0.9, 0.12, 2.0, MAT.metal, -3.2, 0.5, cz, { collide: true });
    box(0.86, 0.1, 1.96, new THREE.MeshStandardMaterial({ color: 0x7e8894, roughness: 0.9 }), -3.2, 0.61, cz, {});
    box(0.42, 0.5, 0.42, MAT.metal, 3.4, 0.25, zLo + 0.6, { collide: true });
    cellLight = new THREE.PointLight(0xdfe8f5, 16, 7, 1.8);
    cellLight.position.set(0, Y - 0.15, cz); scene.add(cellLight);
    const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.05, 10, 10), new THREE.MeshBasicMaterial({ color: 0xf2f6ff }));
    bulb.position.set(0, Y - 0.12, cz); worldGroup.add(bulb);
    rooms.push({
        name: 'Solitary — Period Room', minX: -4, maxX: 4, minZ: zLo, maxZ: zHi,
        rig: { key: { p: [0, Y - 0.2, cz], t: [0.3, 0, cz + 0.3], i: 5, angle: 1.1, dist: 6 },
            fills: [[0, 2.2, cz, 1.2, 0xd7e4f2]] } });
})();

/* ---- Reading room: x[8,26] z[spineEnd-22, spineEnd-2] ---- */
(function readingRoom() {
    const Y = CEIL.reading, xLo = 8, xHi = 26, zLo = spineEnd - 22, zHi = spineEnd - 2, cz = (zLo + zHi) / 2;
    floorRect(xLo, xHi, zLo, zHi, MAT.galleryFloor);
    cofferedCeiling(xLo, xHi, zLo, zHi, Y, { beam: TIMBER, ceil: MAT.ceiling, bay: 4.5, skylight: true });
    const rug = new THREE.Mesh(new THREE.PlaneGeometry(8, 6), new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [3, 2] }), color: 0x5c2434, roughness: 1, envMapIntensity: 0.15 }));
    rug.rotation.x = -Math.PI / 2; rug.position.set(16, 0.008, cz); rug.receiveShadow = true; worldGroup.add(rug);
    wallRun('x', zLo, xLo, xHi, Y, {});
    // north wall (z=zHi) — gains a doorway when the deepest east gallery links in
    wallRun('x', zHi, xLo, xHi, Y, { doors: loopLinks[1] ? [{ at: loopLinks[1].linkX, w: 2.4, h: 2.8 }] : [] });
    buildLoopCorridor(1);
    wallRun('z', xHi, zLo, zHi, Y, {});
    // west wall (x=8) built by archive with the door

    const reading = (DATA.reading || []).filter(r => r.img || r.file);
    const books = reading.filter(r => r.book), sheets = reading.filter(r => !r.book);
    const shelfWood = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1, 1] }), color: 0x6b4a2e, roughness: 0.6, envMapIntensity: 0.5 });
    const clothColors = [0x7a3b2e, 0x2e4a5c, 0x51402a, 0x3c5a3a, 0x5a2e3c, 0x2f3a55, 0x6e5a2f, 0x4a2f55];
    const fillerGeo = new THREE.BoxGeometry(1, 1, 1);
    // hundreds of filler spines share these eight materials instead of
    // allocating (and compiling state for) one material per spine
    const fillerMats = clothColors.map((c) => new THREE.MeshStandardMaterial({ color: c, roughness: 0.8 }));
    const pagesMat = new THREE.MeshStandardMaterial({ color: 0xefe8d4, roughness: 0.95 });
    let bi = 0;
    /* Cloth spine with the title stamped in gilt, reading top-to-bottom —
       browsable from across the room, like a real shelf. */
    function spineTexture(title, year, color) {
        return canvasTexture(96, 512, (g, w, h) => {
            const hex = '#' + color.toString(16).padStart(6, '0');
            g.fillStyle = hex; g.fillRect(0, 0, w, h);
            g.fillStyle = 'rgba(0,0,0,0.22)'; g.fillRect(0, 0, 7, h); g.fillRect(w - 7, 0, 7, h);
            g.fillStyle = 'rgba(255,255,255,0.07)'; g.fillRect(9, 0, 5, h);
            g.fillStyle = 'rgba(212,175,90,0.92)';
            g.fillRect(12, 26, w - 24, 4); g.fillRect(12, h - 30, w - 24, 4);
            g.save(); g.translate(w / 2, h / 2); g.rotate(Math.PI / 2);
            g.textAlign = 'center'; g.textBaseline = 'middle';
            let t = String(title || 'Untitled');
            if (t.length > 30) t = t.slice(0, 29) + '…';
            g.fillStyle = '#eadfb8'; g.font = `700 ${t.length > 22 ? 30 : 36}px Georgia, serif`;
            g.fillText(t, 0, -8);
            const yr = String(year || '').match(/\d{4}/);
            if (yr) { g.font = '400 22px Georgia, serif'; g.fillStyle = 'rgba(234,223,184,.7)'; g.fillText(yr[0], 0, 26); }
            g.restore();
        });
    }
    function bookshelf(x, z, ry) {
        const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g); g.updateMatrixWorld(true);
        const W = 2.4, H = 2.5, D = 0.34;
        const side = sx => { const m = new THREE.Mesh(new THREE.BoxGeometry(0.05, H, D), shelfWood); m.position.set(sx, H / 2, 0); m.castShadow = true; g.add(m); };
        side(-W / 2 + 0.025); side(W / 2 - 0.025);
        const top = new THREE.Mesh(new THREE.BoxGeometry(W, 0.06, D), shelfWood); top.position.set(0, H - 0.03, 0); top.castShadow = true; g.add(top);
        const back = new THREE.Mesh(new THREE.BoxGeometry(W - 0.06, H - 0.1, 0.03), new THREE.MeshStandardMaterial({ color: 0x2c2118, roughness: 0.9 })); back.position.set(0, H / 2, -D / 2 + 0.02); g.add(back);
        const plinthB = new THREE.Mesh(new THREE.BoxGeometry(W, 0.14, D), shelfWood); plinthB.position.set(0, 0.07, 0); g.add(plinthB);
        const rows = [0.5, 1.0, 1.5, 2.0];
        for (const ry2 of rows) { const board = new THREE.Mesh(new THREE.BoxGeometry(W - 0.08, 0.04, D - 0.04), shelfWood); board.position.set(0, ry2, 0); board.castShadow = true; g.add(board); }
        const usable = W - 0.16;
        for (const rowY of rows) {
            let cx = -usable / 2, slot = 0;
            while (cx < usable / 2 - 0.1) {
                const wantReal = bi < books.length && (slot % 3 !== 2);
                if (wantReal && (bi % 7 === 3) && cx < usable / 2 - 0.3) {
                    // occasional face-out feature copy (cover to the room)
                    const rec = books[bi++];
                    const coverMat = new THREE.MeshStandardMaterial({ map: placeholderArt, roughness: 0.8, emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.24 });
                    const clothMat = new THREE.MeshStandardMaterial({ color: clothColors[bi % clothColors.length], roughness: 0.75 });
                    const bw = 0.24, bh = 0.335;
                    const book = new THREE.Mesh(new THREE.BoxGeometry(bw, bh, 0.035), [clothMat, clothMat, pagesMat, clothMat, coverMat, clothMat]);
                    book.position.set(cx + bw / 2, rowY + 0.02 + bh / 2, 0.05); book.rotation.y = Math.PI; book.castShadow = true; g.add(book);
                    if (rec.img) artQueue.push({ url: rec.img, pos: g.localToWorld(book.position.clone()), apply: t => { t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = ANISO; coverMat.map = t; coverMat.emissiveMap = t; coverMat.needsUpdate = true; } });
                    interactables.push({ mesh: book, data: { kind: 'book', gallery: 'Reading Room', coverAxis: [0, 0, 1], ...rec } });
                    cx += bw + 0.04;
                } else if (wantReal) {
                    // spine-out, title readable on the shelf (Criterion-closet style)
                    const rec = books[bi++];
                    const cloth = clothColors[(bi * 5 + rec.n.length) % clothColors.length];
                    const st = 0.055 + (rec.n.length % 4) * 0.009, bh = 0.315 + (rec.n.length % 3) * 0.012;
                    const spineMat = new THREE.MeshStandardMaterial({
                        map: spineTexture(rec.n, rec.l1, cloth), roughness: 0.72,
                        emissive: 0xffffff, emissiveMap: null, emissiveIntensity: 0,
                    });
                    spineMat.emissiveMap = spineMat.map; spineMat.emissiveIntensity = 0.14;
                    const coverMat = new THREE.MeshStandardMaterial({ map: placeholderArt, roughness: 0.8, emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.24 });
                    const clothMat = new THREE.MeshStandardMaterial({ color: cloth, roughness: 0.78 });
                    const book = new THREE.Mesh(new THREE.BoxGeometry(st, bh, 0.24),
                        [coverMat, clothMat, pagesMat, clothMat, spineMat, clothMat]);
                    book.position.set(cx + st / 2, rowY + 0.02 + bh / 2, 0.02);
                    book.castShadow = true; g.add(book);
                    if (rec.img) artQueue.push({ url: rec.img, pos: g.localToWorld(book.position.clone()), apply: t => { t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = ANISO; coverMat.map = t; coverMat.emissiveMap = t; coverMat.needsUpdate = true; } });
                    interactables.push({ mesh: book, data: { kind: 'book', gallery: 'Reading Room', coverAxis: [1, 0, 0], ...rec } });
                    cx += st + 0.006;
                } else {
                    const n = 2 + Math.floor(Math.random() * 4);
                    for (let k = 0; k < n && cx < usable / 2 - 0.05; k++) {
                        const sw = 0.03 + Math.random() * 0.035, sh = 0.26 + Math.random() * 0.075;
                        const spine2 = new THREE.Mesh(fillerGeo, fillerMats[Math.floor(Math.random() * fillerMats.length)]);
                        spine2.scale.set(sw, sh, 0.22); spine2.position.set(cx + sw / 2, rowY + 0.02 + sh / 2, 0); g.add(spine2); cx += sw + 0.006;
                    }
                }
                slot++; cx += 0.012;
            }
        }
        const c = Math.cos(ry), s = Math.sin(ry);
        addCollider(x - ((W / 2) * Math.abs(c) + (D / 2 + 0.1) * Math.abs(s)), x + ((W / 2) * Math.abs(c) + (D / 2 + 0.1) * Math.abs(s)),
            z - ((W / 2) * Math.abs(s) + (D / 2 + 0.1) * Math.abs(c)), z + ((W / 2) * Math.abs(s) + (D / 2 + 0.1) * Math.abs(c)));
    }
    bookshelf(13, zLo + 0.22, 0); bookshelf(16, zLo + 0.22, 0); bookshelf(19, zLo + 0.22, 0);
    bookshelf(xHi - 0.22, cz - 3, -Math.PI / 2); bookshelf(xHi - 0.22, cz + 3, -Math.PI / 2);

    function zineRack(x, z, ry, items) {
        if (!items.length) return;
        const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g); g.updateMatrixWorld(true);
        const frame = new THREE.Mesh(new THREE.BoxGeometry(2.5, 1.8, 0.05), MAT.benchWood); frame.position.set(0, 1.55, 0); g.add(frame);
        items.slice(0, 8).forEach((rec, k) => {
            const col = k % 4, row = Math.floor(k / 4);
            const covMat = new THREE.MeshStandardMaterial({ map: placeholderArt, roughness: 0.85, emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.26 });
            const cov = new THREE.Mesh(new THREE.PlaneGeometry(0.42, 0.56), covMat); cov.position.set(-0.93 + col * 0.62, 2.05 - row * 0.85, 0.05); cov.rotation.x = -0.09; g.add(cov);
            const lip = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.025, 0.07), MAT.frameBlack); lip.position.set(-0.93 + col * 0.62, 1.74 - row * 0.85, 0.05); g.add(lip);
            if (rec.img) artQueue.push({ url: rec.img, pos: g.localToWorld(cov.position.clone()), apply: t => { t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = ANISO; covMat.map = t; covMat.emissiveMap = t; covMat.needsUpdate = true; const a = t.image.width / t.image.height, tg = 0.42 / 0.56; if (a > tg) cov.scale.set(1, tg / a, 1); else cov.scale.set(a / tg, 1, 1); } });
            interactables.push({ mesh: cov, data: { kind: 'book', gallery: 'Reading Room', ...rec } });
        });
    }
    zineRack(xLo + 3, zHi - 0.19, Math.PI, sheets);
    zineRack(xLo + 6.5, zHi - 0.19, Math.PI, sheets.slice(8));

    function readingTable(x, z) {
        const g = new THREE.Group(); g.position.set(x, 0, z); worldGroup.add(g);
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.9, 0.055, 1.0), MAT.benchWood); top.position.y = 0.76; top.castShadow = true; top.receiveShadow = true; g.add(top);
        for (const [lx, lz] of [[-0.85, -0.4], [0.85, -0.4], [-0.85, 0.4], [0.85, 0.4]]) { const leg = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.74, 0.07), shelfWood); leg.position.set(lx, 0.37, lz); g.add(leg); }
        const base = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.08, 0.03, 16), MAT.brass); base.position.set(0, 0.8, -0.25); g.add(base);
        const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, 0.26, 8), MAT.brass); stem.position.set(0, 0.94, -0.25); g.add(stem);
        const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.11, 0.11, 12, 1, true, 0, Math.PI), new THREE.MeshStandardMaterial({ color: 0x1f4d38, roughness: 0.35, metalness: 0.4, side: THREE.DoubleSide, envMapIntensity: 0.9 })); shade.position.set(0, 1.07, -0.25); shade.rotation.y = Math.PI / 2; shade.rotation.z = Math.PI / 2; g.add(shade);
        const glow = new THREE.Mesh(new THREE.CylinderGeometry(0.075, 0.095, 0.09, 10, 1, true, 0, Math.PI), new THREE.MeshBasicMaterial({ color: 0xffe9b8, side: THREE.DoubleSide })); glow.position.set(0, 1.06, -0.245); glow.rotation.y = Math.PI / 2; glow.rotation.z = Math.PI / 2; g.add(glow);
        const pool = new THREE.Mesh(new THREE.PlaneGeometry(1.5, 1.0), new THREE.MeshBasicMaterial({ map: WASH_TEX, transparent: true, opacity: 0.34, blending: THREE.AdditiveBlending, depthWrite: false })); pool.rotation.x = -Math.PI / 2; pool.position.set(0, 0.792, -0.1); g.add(pool);
        for (const s of [-1, 1]) { const page = new THREE.Mesh(new THREE.PlaneGeometry(0.21, 0.3), new THREE.MeshStandardMaterial({ color: 0xf7f3e6, roughness: 0.95 })); page.position.set(s * 0.105, 0.795, 0.12); page.rotation.x = -Math.PI / 2; page.rotation.y = s * 0.09; g.add(page); }
        addCollider(x - 1.05, x + 1.05, z - 0.6, z + 0.6); bench(x, z + 1.05, 0); bench(x, z - 1.05, Math.PI);
    }
    readingTable(15, cz + 1); readingTable(20, cz + 1);
    ceilingLight(15, Y, cz, 1.8, 0.26); ceilingLight(20, Y, cz, 1.8, 0.26);
    rooms.push({
        // extend the bounds north to swallow the loop corridor so it isn't a
        // dark, unowned dead-zone between rooms
        name: 'Reading Room', minX: xLo, maxX: xHi, minZ: zLo, maxZ: loopLinks[1] ? loopLinks[1].zGal : zHi,
        rig: { key: { p: [17, Y - 0.5, cz], t: [17, 0.5, cz], i: 105, angle: 0.95, dist: 16 },
            fills: [[13, 2.3, cz, 22, 0xffdfae], [20, 2.3, cz, 22, 0xffdfae], [16, 2.6, zLo + 3, 18, 0xffe6c0]] } });
})();

/* ---- Cinema: raked theater, x[-26,-8] z[spineEnd-22, spineEnd-2] ---- */
(function cinema() {
    const Y = CEIL.cinema, xScreen = -26, xBack = -8, zLo = spineEnd - 22, zHi = spineEnd - 2, cz = (zLo + zHi) / 2;
    const darkFloor = new THREE.MeshStandardMaterial({ ...pbr('woodfloor', { repeat: [5, 5], ao: true }), color: 0x4a3b30, envMapIntensity: 0.2 });
    // base floor (pit) + tiers as sunken platforms rising toward the entrance
    floorRect(xScreen + 0.15, xBack - 0.15, zLo + 0.15, zHi - 0.15, MAT.concreteFloor, -1.62);
    ceilRect(xScreen + 0.15, xBack - 0.15, zLo + 0.15, zHi - 0.15, Y, new THREE.MeshStandardMaterial({ color: 0x141418, roughness: 0.96 }));
    wallRun('x', zLo, xScreen, xBack, Y, { mat: MAT.wallDark });
    wallRun('x', zHi, xScreen, xBack, Y, { mat: MAT.wallDark });
    wallRun('z', xScreen, zLo, zHi, Y, { mat: MAT.wallDark });
    // east wall (x=-8) built by archive with door at z=spineEnd-8

    // screen on the west wall
    slideshowScreen(DATA.slides.length ? DATA.slides : [{ t: 'National Political Prisoner Coalition', img: '' }],
        DATA.video, new THREE.Vector3(xScreen + 0.18, 0.0, cz), new THREE.Vector3(1, 0, 0), 10, 5.0, { speed: 7 });
    box(0.14, 5.6, 11, MAT.frameBlack, xScreen + 0.05, 0.2, cz, {});

    // tiers stepping DOWN toward the screen. The top tier (x -10.5..-8) is an
    // OPEN ENTRY LANDING — no seats, no colliders — because the door from the
    // archive lands on it at z = spineEnd-8. (v2 planted a seat-block collider
    // straight across that doorway, which is why the theater was unenterable.)
    // From the landing you walk anywhere along the back, then down the wide
    // central aisle.
    const tiers = [
        { x0: -10.5, x1: -8, y: 0.0, seats: false },
        { x0: -13, x1: -10.5, y: -0.34, seats: true },
        { x0: -15.5, x1: -13, y: -0.68, seats: true },
        { x0: -18, x1: -15.5, y: -1.02, seats: true },
        { x0: -20.5, x1: -18, y: -1.36, seats: true },
    ];
    const carpet = new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [2, 3] }), color: 0x3a1520, roughness: 1, envMapIntensity: 0.1 });
    const riser = new THREE.MeshStandardMaterial({ color: 0x1a0e12, roughness: 0.9 });
    const AISLE = 1.15;                                  // half-width of the centre aisle
    tiers.forEach(t => {
        const w = t.x1 - t.x0;
        const top = new THREE.Mesh(new THREE.BoxGeometry(w, 0.3, zHi - zLo - 0.4), carpet);
        top.position.set((t.x0 + t.x1) / 2, t.y - 0.15, cz); top.receiveShadow = true; worldGroup.add(top);
        // riser face toward the screen
        const rf = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.34, zHi - zLo - 0.4), riser);
        rf.position.set(t.x0, t.y - 0.32, cz); worldGroup.add(rf);
        addFloorZone(t.x0, t.x1, zLo, zHi, t.y);
        if (!t.seats) return;
        // seats: two blocks with the centre aisle kept clear
        const rowX = t.x0 + w * 0.42;
        const accent = 0xe0913a;
        for (let zz = zLo + 1.4; zz <= zHi - 1.4; zz += 0.78) {
            if (Math.abs(zz - cz) < AISLE + 0.35) continue;
            cinemaSeat(rowX, t.y, zz, accent);
        }
        // block colliders (leave the aisle open)
        addCollider(t.x0 + 0.1, t.x1 - 0.1, zLo + 1.1, cz - AISLE);
        addCollider(t.x0 + 0.1, t.x1 - 0.1, cz + AISLE, zHi - 1.1);
    });
    // aisle guide lights down the steps (little warm dots, like a real cinema)
    for (const t of tiers.slice(1)) {
        for (const s of [-1, 1]) {
            const dot = new THREE.Mesh(new THREE.CircleGeometry(0.035, 8), new THREE.MeshBasicMaterial({ color: 0xffc873 }));
            dot.rotation.x = -Math.PI / 2;
            dot.position.set(t.x0 + 0.25, t.y + 0.005, cz + s * (AISLE - 0.12));
            worldGroup.add(dot);
        }
    }
    // pit floor zone in front
    addFloorZone(xScreen, -20.5, zLo, zHi, -1.55);

    // projector + beam from the back
    box(0.5, 0.34, 0.42, MAT.frameBlack, xBack - 0.6, 2.7, cz, {});
    const beam = new THREE.Mesh(new THREE.ConeGeometry(2.8, 16.5, 24, 1, true), new THREE.MeshBasicMaterial({ color: 0xfff3da, transparent: true, opacity: 0.04, side: THREE.DoubleSide, blending: THREE.AdditiveBlending, depthWrite: false }));
    beam.position.set((xScreen + xBack) / 2 + 0.5, 2.5, cz); beam.rotation.z = Math.PI / 2 - 0.03; worldGroup.add(beam);
    const dustN = 150, dustPos = new Float32Array(dustN * 3);
    for (let i = 0; i < dustN; i++) { const t = Math.random(); const rad = (0.2 + 2.4 * t) * Math.sqrt(Math.random()); const ang = Math.random() * Math.PI * 2; dustPos[i * 3] = (xBack - 0.6) - t * 17; dustPos[i * 3 + 1] = 2.6 - t * 0.4 + Math.sin(ang) * rad; dustPos[i * 3 + 2] = cz + Math.cos(ang) * rad; }
    const dustGeo = new THREE.BufferGeometry(); dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
    window.__dust = new THREE.Points(dustGeo, new THREE.PointsMaterial({ color: 0xffeecd, size: 0.014, transparent: true, opacity: 0.5, blending: THREE.AdditiveBlending, depthWrite: false }));
    worldGroup.add(window.__dust);
    anchors.cinemaProjector = [xBack - 0.6, 2.7, cz];
    anchors.cinemaScreen = [xScreen + 0.4, 2.4, cz];

    wallPanel(panelTexture('Theater', 'Faces & places',
        'A rolling reel of the movements, prisons, and campaigns documented across the NPPC — the same landscapes and crowds that stand behind every topic on this site. Take a seat; the steps lead down toward the screen.'),
        new THREE.Vector3(xBack - 0.16, 1.8, zHi - 2), new THREE.Vector3(-1, 0, 0), 1.7, 2.2,
        { interact: { kind: 'panel', n: 'Faces & places', l1: 'Theater', d: 'A rolling reel of the movements, prisons, and campaigns documented across the NPPC.' } });

    rooms.push({
        name: 'Theater', minX: xScreen, maxX: xBack, minZ: zLo, maxZ: zHi,
        rig: { key: { p: [-14, Y - 0.4, cz], t: [-14, -1, cz], i: 22, angle: 1.0, dist: 20 },
            fills: [[-9, 2.4, cz, 8, 0xffdca8], [-22, 1.2, cz, 10, 0xbcd0ff]] } });
})();

/* --------------------------------------------------------------- lighting */
scene.add(new THREE.HemisphereLight(0xe8eeff, 0x3a352e, 0.42));
// cellLight is created inside the cell() layout function (module-scoped above).

// theater projector beam glow
if (anchors.cinemaProjector && anchors.cinemaScreen) {
    const beamLight = new THREE.SpotLight(0xfff0d5, 90, 22, 0.5, 0.55, 1.4);
    beamLight.position.set(...anchors.cinemaProjector);
    beamLight.target.position.set(...anchors.cinemaScreen);
    scene.add(beamLight, beamLight.target);
}

// bronze sculpture accent in the rotunda
if (anchors.sculpt) {
    const sculptLight = new THREE.SpotLight(0xffe9c2, 900, 14, 0.42, 0.5, 1.9);
    sculptLight.position.set(...anchors.sculpt);
    sculptLight.target.position.set(...(anchors.sculptTarget || [0, 1.2, 13]));
    sculptLight.castShadow = true;
    sculptLight.shadow.mapSize.set(1024, 1024);
    sculptLight.shadow.bias = -0.0004;
    scene.add(sculptLight, sculptLight.target);
}

/* Reposition-only pool → shader program count stays constant. */
const keyA = new THREE.SpotLight(0xfff1da, 900, 26, 0.7, 0.55, 1.8);
keyA.castShadow = true; keyA.shadow.mapSize.set(1024, 1024); keyA.shadow.bias = -0.0004;
const keyB = keyA.clone();
scene.add(keyA, keyA.target, keyB, keyB.target);
const fillPool = [];
for (let i = 0; i < 4; i++) {
    const p = new THREE.PointLight(0xfff0d8, 0, 14, 2);
    scene.add(p); fillPool.push(p);
}
/* r160 uses physical light units (candela); rig values are authored in a
   compact scale and multiplied up here. */
const KEY_SCALE = 5.2, FILL_SCALE = 4.2;
function applyRig(light, rig) {
    light.position.set(...rig.key.p);
    light.target.position.set(...rig.key.t);
    light.intensity = rig.key.i * KEY_SCALE;
    light.angle = rig.key.angle;
    light.distance = rig.key.dist;
    light.color.set(rig.key.c || 0xfff1da);
}
let curRoom = null, prevRoom = null;
/* The pond reflector re-renders the whole scene — only pay for it while
   actually standing in the atrium (and never on a floored-out GPU). */
function updateReflector() {
    if (window.__reflector) window.__reflector.visible = !!curRoom && curRoom.name === 'Grand Atrium' && curDPR > 0.75;
}
function roomAt(x, z) {
    for (const r of rooms) if (x >= r.minX && x <= r.maxX && z >= r.minZ && z <= r.maxZ) return r;
    return null;
}
/* ---- region culling: only the current room and its sightline neighbours
   are rendered. Content is partitioned into per-room groups after build;
   anything tagged userData.shared (sky, skyline) stays always-on. ---- */
let regionState = null;
function partitionWorldByRoom() {
    const groups = rooms.map(r => { const g = new THREE.Group(); g.name = 'region:' + r.name; return g; });
    const shared = new THREE.Group(); shared.name = 'region:shared';
    for (const child of [...worldGroup.children]) {
        if (child.userData && child.userData.shared) { shared.add(child); continue; }
        const p = child.position;
        let target = null;
        for (let i = 0; i < rooms.length; i++) {
            const r = rooms[i];
            if (p.x >= r.minX - 0.25 && p.x <= r.maxX + 0.25 && p.z >= r.minZ - 0.25 && p.z <= r.maxZ + 0.25) { target = groups[i]; break; }
        }
        (target || shared).add(child);
    }
    groups.forEach(g => worldGroup.add(g));
    worldGroup.add(shared);
    const adj = rooms.map(() => new Set());
    const near = (a, b, pad) => !(a.maxX + pad < b.minX || b.maxX + pad < a.minX || a.maxZ + pad < b.minZ || b.maxZ + pad < a.minZ);
    for (let i = 0; i < rooms.length; i++) {
        for (let j = 0; j < rooms.length; j++) if (i !== j && near(rooms[i], rooms[j], 1.5)) adj[i].add(j);
    }
    // long axial vistas cross more than one boundary — link them explicitly
    const byName = n => rooms.findIndex(r => r.name === n);
    const link = (a, b) => { const ia = byName(a), ib = byName(b); if (ia >= 0 && ib >= 0) { adj[ia].add(ib); adj[ib].add(ia); } };
    link('Museum Plaza', 'The Hall of Figures');       // through the atrium glass + door
    link('Theater', 'Reading Room');                    // aligned doors across the archive
    link('Grand Atrium', 'Garden of Remembrance');      // through the west vestibule
    const gals = (DATA.galleries || []);
    for (let i = 0; i + 1 < gals.length; i += 2) link(gals[i].title, gals[i + 1].title);   // door-to-door across the spine
    for (let i = 0; i + 2 < gals.length; i++) link(gals[i].title, gals[i + 2].title);       // enfilade: same-side gallery-to-gallery
    if (loopLinks[1] && gals[loopLinks[1].i]) link(gals[loopLinks[1].i].title, 'Reading Room');  // perimeter loop: deepest east gallery → reading room
    return { groups, adj };
}
function updateRegionVisibility() {
    if (!regionState || !curRoom) return;
    const idx = rooms.indexOf(curRoom);
    if (idx < 0) return;
    regionState.groups.forEach((g, i) => { g.visible = (i === idx) || regionState.adj[idx].has(i); });
}

function setRoom(r) {
    if (!r || r === curRoom) return;
    prevRoom = curRoom; curRoom = r;
    applyRig(keyA, r.rig);
    if (prevRoom) applyRig(keyB, prevRoom.rig);
    r.rig.fills.forEach((f, i) => {
        if (!fillPool[i]) return;
        fillPool[i].position.set(f[0], f[1], f[2]);
        fillPool[i].intensity = f[3] * FILL_SCALE;
        fillPool[i].color.set(f[4] || 0xfff0d8);
    });
    for (let i = r.rig.fills.length; i < fillPool.length; i++) fillPool[i].intensity = 0;
    // sky dome + skyline only render in the outdoor-facing rooms, so a culling
    // gap in the interior never reveals the sky as a big blue "hole"
    if (window.__sky) {
        const showSky = r.name === 'Museum Plaza' || r.name === 'Grand Atrium';
        for (const s of window.__sky) s.visible = showSky;
    }
    toast(r.name);
    updateRegionVisibility();
    syncVideos();
    updateReflector();
    renderer.shadowMap.needsUpdate = true;
}

/* ----------------------------------------------------------------- player */
const player = {
    pos: new THREE.Vector3(0, 0, 30.5),
    yaw: 0,                  // rotateY(0) → camera looks -z, into the museum
    pitch: 0,
    vel: new THREE.Vector3(),
    eye: 1.65,
    bob: 0,
    ground: 0,               // current floor height (smoothly follows tiers)
};
const keys = {};
window.addEventListener('keydown', (e) => {
    keys[e.code] = true;
    if (e.code === 'KeyE') tryInspect();
    if (e.code === 'KeyQ' && held) putBackBook();
    if (e.code === 'Escape') {
        if (overlayOpen) { if (readerOpen) closeReader(); else closeOverlay(); }
        else if (held) putBackBook();
    }
});
window.addEventListener('keyup', (e) => { keys[e.code] = false; });

let locked = false, overlayOpen = false, started = false;
const splash = document.getElementById('museum-splash');
const pauseEl = document.getElementById('museum-pause');
const hud = document.getElementById('museum-hud');
const toastEl = document.getElementById('museum-toast');
const reticle = document.getElementById('museum-reticle');
const hint = document.getElementById('museum-hint');

function lock() { canvas.requestPointerLock?.(); }
document.addEventListener('pointerlockchange', () => {
    locked = document.pointerLockElement === canvas;
    if (locked) {
        splash.classList.add('hide'); pauseEl.classList.add('hide'); hud.classList.remove('hide');
        started = true;
    } else if (started && !overlayOpen) {
        pauseEl.classList.remove('hide'); hud.classList.add('hide');
    }
});
document.addEventListener('mousemove', (e) => {
    if (!locked) return;
    player.yaw -= e.movementX * 0.0021;
    player.pitch = Math.max(-1.45, Math.min(1.45, player.pitch - e.movementY * 0.0021));
});
const enterBtn = document.getElementById('museum-enter');
enterBtn.addEventListener('click', lock);
// the blade renders the button disabled ("Loading the museum…") so slow
// connections see an honest state; the engine is live now
enterBtn.disabled = false;
enterBtn.textContent = 'Enter the museum';
document.getElementById('museum-resume').addEventListener('click', lock);
canvas.addEventListener('click', () => {
    if (!locked && !overlayOpen && !started) lock();
    else if (locked) tryInspect();
});

/* touch controls: left half = move stick, right half = look */
let moveTouch = null, lookTouch = null, touchMove = { x: 0, y: 0 };
const isTouch = matchMedia('(pointer: coarse)').matches;
if (isTouch) {
    document.getElementById('museum-touch-note')?.classList.remove('hide');
    canvas.addEventListener('touchstart', (e) => {
        if (!started) { started = true; splash.classList.add('hide'); hud.classList.remove('hide'); }
        for (const t of e.changedTouches) {
            if (t.clientX < innerWidth / 2 && moveTouch === null) moveTouch = { id: t.identifier, x: t.clientX, y: t.clientY };
            else if (lookTouch === null) lookTouch = { id: t.identifier, x: t.clientX, y: t.clientY };
        }
        e.preventDefault();
    }, { passive: false });
    canvas.addEventListener('touchmove', (e) => {
        for (const t of e.changedTouches) {
            if (moveTouch && t.identifier === moveTouch.id) {
                touchMove.x = Math.max(-1, Math.min(1, (t.clientX - moveTouch.x) / 60));
                touchMove.y = Math.max(-1, Math.min(1, (t.clientY - moveTouch.y) / 60));
            } else if (lookTouch && t.identifier === lookTouch.id) {
                player.yaw -= (t.clientX - lookTouch.x) * 0.006;
                player.pitch = Math.max(-1.45, Math.min(1.45, player.pitch - (t.clientY - lookTouch.y) * 0.006));
                lookTouch.x = t.clientX; lookTouch.y = t.clientY;
            }
        }
        e.preventDefault();
    }, { passive: false });
    canvas.addEventListener('touchend', (e) => {
        for (const t of e.changedTouches) {
            if (moveTouch && t.identifier === moveTouch.id) { moveTouch = null; touchMove.x = touchMove.y = 0; }
            if (lookTouch && t.identifier === lookTouch.id) {
                lookTouch = null;
                tryInspect();       // tap = inspect what you're looking at
            }
        }
    });
}

function collide(nx, nz) {
    const r = 0.35;
    const g = player.ground;
    for (const c of colliders) {
        if (c.y0 !== -Infinity || c.y1 !== Infinity) {
            if (g + 1.7 <= c.y0 || g >= c.y1) continue;      // band above/below the walker
        }
        if (nx > c.minX - r && nx < c.maxX + r && nz > c.minZ - r && nz < c.maxZ + r) return true;
    }
    return false;
}
function updatePlayer(dt) {
    const run = keys.ShiftLeft || keys.ShiftRight;
    const speed = run ? 5.4 : 3.1;
    let fx = 0, fz = 0;
    if (locked || isTouch) {                    // input only while in control…
        if (keys.KeyW || keys.ArrowUp) fz += 1;
        if (keys.KeyS || keys.ArrowDown) fz -= 1;
        if (keys.KeyA || keys.ArrowLeft) fx -= 1;
        if (keys.KeyD || keys.ArrowRight) fx += 1;
        fz += -touchMove.y; fx += touchMove.x;
    }
    const len = Math.hypot(fx, fz) || 1;
    fx /= len; fz /= len;
    const sin = Math.sin(player.yaw), cos = Math.cos(player.yaw);
    const vx = (sin * -fz + cos * fx) * speed;
    const vz = (cos * -fz - sin * fx) * speed;
    const nx = player.pos.x + vx * dt, nz = player.pos.z + vz * dt;
    if (!collide(nx, player.pos.z)) player.pos.x = nx;
    if (!collide(player.pos.x, nz)) player.pos.z = nz;
    const moving = (Math.abs(fx) + Math.abs(fz)) > 0.01;
    player.bob += dt * (moving ? (run ? 11 : 7.4) : 0);
    const bobY = moving ? Math.sin(player.bob) * 0.026 : 0;
    // smoothly follow tiered floors (cinema) so steps feel like walking down them
    const targetGround = floorHeightAt(player.pos.x, player.pos.z);
    player.ground += (targetGround - player.ground) * Math.min(1, dt * 9);
    camera.position.set(player.pos.x, player.ground + player.eye + bobY, player.pos.z);
    camera.rotation.set(0, 0, 0);
    camera.rotateY(player.yaw);
    camera.rotateX(player.pitch);
}

/* ------------------------------------------------------------- interaction */
const raycaster = new THREE.Raycaster();
raycaster.far = 5.2;
let hovered = null, hoverTick = 0;
const _hp = new THREE.Vector3();
function updateHover() {
    if (held) { reticle.classList.remove('on'); hint.textContent = ''; hovered = null; return; }
    raycaster.setFromCamera(new THREE.Vector2(0, 0), camera);
    // only raycast against interactables within reach of the camera — the
    // museum holds thousands of clickable meshes, but the reticle can only hit
    // what's ~5m ahead. World positions are cached on first use (static props).
    const cam = camera.position, near = [], nearMap = new Map();
    for (const it of interactables) {
        if (!it._wp) { it._wp = it.mesh.getWorldPosition(new THREE.Vector3()); }
        const p = it.grab ? it.mesh.getWorldPosition(_hp) : it._wp;   // grabbed props can move
        if (p.distanceToSquared(cam) < 42) { near.push(it.mesh); nearMap.set(it.mesh, it); }
    }
    const hits = near.length ? raycaster.intersectObjects(near, false) : [];
    hovered = hits.length ? nearMap.get(hits[0].object) : null;
    reticle.classList.toggle('on', !!hovered);
    hint.textContent = hovered
        ? (hovered.data.kind === 'book' || hovered.data.kind === 'product' ? 'Click to pick it up'
            : hovered.data.kind === 'panel' ? 'Click to read' : 'Click to inspect')
        : '';
}

/* ---- physically pick a book off the shelf (Criterion-closet style) ---- */
let held = null;
const bookbar = document.getElementById('museum-bookbar');
const bbTitle = document.getElementById('bb-title');
const bbMeta = document.getElementById('bb-meta');
const bbReadLabel = document.getElementById('bb-read-label');
function pickUpBook(entry) {
    if (held || overlayOpen) return;
    const m = entry.grab || entry.mesh;   // 3D props: grab the whole prop, not the click proxy
    held = {
        mesh: m, data: entry.data,
        homeParent: m.parent,
        homePos: m.position.clone(), homeQuat: m.quaternion.clone(),
        coverAxis: new THREE.Vector3(...(entry.data.coverAxis || [0, 0, 1])),
        phase: 'hold',
    };
    worldGroup.attach(m);                                   // keep world transform
    held.worldHomePos = m.position.clone();
    held.worldHomeQuat = m.quaternion.clone();
    // a carried item stops casting: its shadow would need a re-render every
    // frame; one refresh removes the stale silhouette. castShadow is per-object
    // (not inherited), so toggle every caster in the grabbed subtree.
    held.shadowMeshes = [];
    m.traverse((o) => { if (o.castShadow) { held.shadowMeshes.push(o); o.castShadow = false; } });
    renderer.shadowMap.needsUpdate = true;
    if (bookbar) {
        bbTitle.textContent = entry.data.n || 'Untitled';
        bbMeta.textContent = [entry.data.l1, entry.data.l2].filter(Boolean).join('  ·  ');
        if (bbReadLabel) bbReadLabel.textContent = entry.data.kind === 'product'
            ? 'Buy' + (entry.data.l1 ? ' — ' + entry.data.l1 : '') : 'Read it';
        bookbar.classList.remove('hide');
    }
}
function putBackBook() {
    if (!held) return;
    held.phase = 'back';
    if (bookbar) bookbar.classList.add('hide');
}
function readHeld() {
    if (!held) return;
    const d = held.data;
    putBackBook();
    if (d.kind === 'product') { if (d.u) window.open(d.u, '_blank'); return; }
    if (d.file) openReader(d); else openOverlay(d);
}
const _tmpQ = new THREE.Quaternion(), _tmpO = new THREE.Object3D();
function updateHeld(dt) {
    if (!held) return;
    const m = held.mesh;
    if (held.phase === 'hold') {
        const fwd = new THREE.Vector3();
        camera.getWorldDirection(fwd);
        const tp = camera.position.clone().addScaledVector(fwd, held.data.holdD || 0.6);
        tp.y -= 0.04;
        _tmpO.position.copy(tp);
        _tmpO.lookAt(camera.position);                       // +z toward the eye
        _tmpQ.copy(_tmpO.quaternion);
        if (held.coverAxis.x === 1) {                        // spine books: cover on +x
            _tmpQ.multiply(new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 1, 0), -Math.PI / 2));
        }
        const k = Math.min(1, dt * 9);
        m.position.lerp(tp, k);
        m.quaternion.slerp(_tmpQ, k);
    } else {                                                 // returning to the shelf
        const k = Math.min(1, dt * 7);
        m.position.lerp(held.worldHomePos, k);
        m.quaternion.slerp(held.worldHomeQuat, k);
        if (m.position.distanceTo(held.worldHomePos) < 0.02) {
            held.homeParent.attach(m);
            m.position.copy(held.homePos);
            m.quaternion.copy(held.homeQuat);
            (held.shadowMeshes || []).forEach((o) => { o.castShadow = true; });
            held = null;
            renderer.shadowMap.needsUpdate = true;
        }
    }
}

document.getElementById('bb-read')?.addEventListener('click', readHeld);
document.getElementById('bb-back')?.addEventListener('click', putBackBook);

function tryInspect() {
    if (overlayOpen) return;
    if (held) { readHeld(); return; }
    if (!hovered) return;
    const d = hovered.data;
    if (d.kind === 'book' || d.kind === 'product') pickUpBook(hovered);
    else openOverlay(d);
}

/* reading-room PDF reader */
const reader = document.getElementById('museum-reader');
const mrFrame = document.getElementById('mr-frame');
const mrTitle = document.getElementById('mr-title');
const mrMeta = document.getElementById('mr-meta');
const mrOpen = document.getElementById('mr-open');
const mrRecord = document.getElementById('mr-record');
let readerOpen = false;
function openReader(d) {
    readerOpen = true; overlayOpen = true;
    mrTitle.textContent = d.n || 'Untitled document';
    mrMeta.textContent = [d.l1, d.l2].filter(Boolean).join('  ·  ');
    mrFrame.src = d.file;
    mrOpen.href = d.file;
    if (d.u) { mrRecord.href = d.u; mrRecord.classList.remove('hide'); }
    else mrRecord.classList.add('hide');
    reader.classList.remove('hide');
    hud.classList.add('hide');
    document.exitPointerLock?.();
}
function closeReader() {
    readerOpen = false; overlayOpen = false;
    reader.classList.add('hide');
    mrFrame.src = 'about:blank';
    lock();
}
document.getElementById('mr-close').addEventListener('click', closeReader);

const ov = document.getElementById('museum-inspect');
const ovImg = document.getElementById('mi-img');
const ovTitle = document.getElementById('mi-title');
const ovEyebrow = document.getElementById('mi-eyebrow');
const ovMeta = document.getElementById('mi-meta');
const ovDesc = document.getElementById('mi-desc');
const ovLink = document.getElementById('mi-link');
function openOverlay(d) {
    overlayOpen = true;
    ovEyebrow.textContent = d.gallery || (d.kind === 'doc' ? 'The Archive' : d.l1 || '');
    ovTitle.textContent = d.n || '';
    ovMeta.textContent = [d.l1, d.l2].filter(Boolean).join('  ·  ');
    ovDesc.textContent = d.d || '';
    if (d.img) { ovImg.src = d.img; ovImg.classList.remove('hide'); }
    else ovImg.classList.add('hide');
    if (d.u) { ovLink.href = d.u; ovLink.classList.remove('hide'); }
    else ovLink.classList.add('hide');
    const readBtn = document.getElementById('mi-read');
    if (d.file) {
        readBtn.classList.remove('hide');
        readBtn.onclick = () => { ov.classList.add('hide'); openReader(d); };
    } else readBtn.classList.add('hide');
    ov.classList.remove('hide');
    hud.classList.add('hide');
    document.exitPointerLock?.();
}
function closeOverlay() {
    overlayOpen = false;
    ov.classList.add('hide');
    lock();
}
document.getElementById('mi-close').addEventListener('click', closeOverlay);
ov.addEventListener('click', (e) => { if (e.target === ov) closeOverlay(); });

let toastT = null;
function toast(text) {
    toastEl.textContent = text;
    toastEl.classList.add('on');
    clearTimeout(toastT);
    toastT = setTimeout(() => toastEl.classList.remove('on'), 2600);
}

/* ------------------------------------------------- progressive art loading */
let loading = 0;                     // in-flight full art textures (cap 4)
let loadingImg = 0;                  // in-flight 64px atlas thumbs (cap 6)
const imgShare = new Map();          // thumb url → apply callbacks awaiting one fetch
const texCache = new Map();          // final url → {tex, jobs, stamp}
let texStamp = 0;
/* VRAM budget: beyond this many cached art textures, the least-recently-used
   far-away ones are disposed and their jobs re-queued (browser HTTP cache
   makes the re-fetch cheap when the player comes back). */
const TEX_BUDGET = 140;
const NEAR2 = 45 * 45, NEAR2_LOW = 26 * 26;   // proximity gates (m²)
function startTexJob(job) {
    const url = thumbUrl(job.url, job.hero ? 1024 : 512);
    const hit = texCache.get(url);
    if (hit && hit.tex.image) {
        hit.stamp = ++texStamp;
        hit.jobs.push(job);
        job.apply(hit.tex);
        return;
    }
    loading++;
    texLoader.load(url,
        (tex) => {
            loading--;
            let final = tex;
            const im = tex.image;
            // belt-and-braces: the server thumb route can redirect to the
            // original if it can't resize, so keep the client-side cap
            if (im && (im.width > 1024 || im.height > 1024)) {
                const sc = 1024 / Math.max(im.width, im.height);
                const c = document.createElement('canvas');
                c.width = Math.round(im.width * sc); c.height = Math.round(im.height * sc);
                c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
                final = new THREE.CanvasTexture(c);
                final.colorSpace = THREE.SRGBColorSpace;
                final.anisotropy = ANISO;
                tex.dispose();
            }
            texCache.set(url, { tex: final, jobs: [job], stamp: ++texStamp });
            THREE.Cache.remove(url);   // keep the texture, release the raw Image
            job.apply(final);
            evictTextures();
        },
        undefined,
        () => { loading--; });
}
function evictTextures() {
    if (texCache.size <= TEX_BUDGET) return;
    const entries = [...texCache.entries()].sort((a, b) => a[1].stamp - b[1].stamp);
    for (const [url, e] of entries) {
        if (texCache.size <= TEX_BUDGET) break;
        // only evict entries whose EVERY consumer registered a reset AND is far
        if (!e.jobs.length || e.jobs.some(j => !j.reset)) continue;
        if (e.jobs.some(j => j.pos.distanceToSquared(player.pos) < NEAR2 * 2)) continue;
        e.jobs.forEach((j) => { j.reset(); artQueue.push(j); });
        e.tex.dispose();
        texCache.delete(url);
    }
}
let pumpIdlePos = null;
function pumpArtQueue() {
    if (document.hidden) return;
    if (artQueue.length && (loading < 4 || loadingImg < 6)) {
        // after a pass that dispatched nothing, sleep until the player moves
        if (pumpIdlePos && pumpIdlePos.distanceToSquared(player.pos) < 2.25) { flushAtlases(); return; }
        const px = player.pos;
        for (const j of artQueue) {
            j._d2 = j.pos.distanceToSquared(px);
            j._k = (j.low ? 1e12 : 0) + j._d2;   // portraits before the tile swarm
        }
        artQueue.sort((a, b) => a._k - b._k);
        // proximity-gated: far jobs stay queued instead of downloading the whole
        // museum while the player stands in the plaza
        let i = 0, dispatched = false;
        while ((loading < 4 || loadingImg < 6) && i < artQueue.length && i < 600) {
            const job = artQueue[i];
            const gated = job._d2 > (job.low ? NEAR2_LOW : NEAR2);
            const budget = job.img ? loadingImg < 6 : loading < 4;
            if (!gated && budget) {
                dispatched = true;
                artQueue.splice(i, 1);
                if (job.img) {
                    const inflight = imgShare.get(job.url);
                    if (inflight) { inflight.push(job.apply); }
                    else {
                        loadingImg++;
                        const waiters = [job.apply];
                        imgShare.set(job.url, waiters);
                        const im = new Image();
                        im.crossOrigin = 'anonymous';
                        im.onload = () => { loadingImg--; imgShare.delete(job.url); waiters.forEach((fn) => fn(im)); };
                        im.onerror = () => { loadingImg--; imgShare.delete(job.url); };
                        im.src = job.url;
                    }
                } else {
                    startTexJob(job);
                }
            } else i++;
        }
        pumpIdlePos = dispatched ? null : player.pos.clone();
    }
    flushAtlases();
}
setInterval(pumpArtQueue, 250);      // decoupled from the render loop

/* ------------------------------------------------------------------- loop */
const clock = new THREE.Clock();
let frame = 0, emaMs = 16;
function tick() {
    requestAnimationFrame(tick);
    const dt = Math.min(clock.getDelta(), 0.05);
    frame++;
    // splash and pause overlays don't need 60fps behind them: render a frame
    // every so often so resumes are instant, and skip the rest
    const idleDiv = !started ? 5 : (!locked && !overlayOpen && !isTouch ? 15 : 0);
    if (idleDiv && frame % idleDiv !== 0) return;
    // adaptive resolution: nudge the pixel ratio toward the frame-time budget
    emaMs = emaMs * 0.92 + Math.min(dt * 1000, 60) * 0.08;
    if (frame % 30 === 0) {
        let t = curDPR;
        if (emaMs > 24 && curDPR > DPR_FLOOR) t = Math.max(DPR_FLOOR, +(curDPR - 0.1).toFixed(2));
        else if (emaMs < 15 && curDPR < DPR_CAP) t = Math.min(DPR_CAP, +(curDPR + 0.08).toFixed(2));
        if (t !== curDPR) { curDPR = t; renderer.setPixelRatio(curDPR); updateReflector(); }
    }
    updatePlayer(dt);      // camera always follows player state (input is gated inside)
    if (frame % 6 === 0) updateHover();
    if (frame % 20 === 0) {
        setRoom(roomAt(player.pos.x, player.pos.z));
        pumpArtQueue();
    }
    for (const s of slideshows) { if (s.mesh && !chainVisible(s.mesh)) continue; s.draw(dt); }
    for (const a of animatedTex) a(dt);
    updateHeld(dt);
    // shadows are static: room changes and pickup/putdown trigger single
    // refreshes (the held item stops casting while carried, so no per-frame
    // shadow re-render is ever needed)
    if (window.__dust) window.__dust.rotation.y = Math.sin(clock.elapsedTime * 0.05) * 0.02;
    if (cellLight) cellLight.intensity = 16 + Math.sin(clock.elapsedTime * 17) * 0.9 + Math.sin(clock.elapsedTime * 3.1) * 0.7;
    renderer.render(scene, camera);
}
regionState = partitionWorldByRoom();
setRoom(rooms[0]);
updateRegionVisibility();
renderer.shadowMap.needsUpdate = true;
tick();

/* boot the first textures immediately (nearest ones) */
pumpArtQueue();

/* debug/test hook: teleport the player without pointer lock */
window.__museumDebug = {
    player, rooms, renderer,
    teleport(x, z, yaw = Math.PI, pitch = 0) {
        player.pos.set(x, 0, z);
        player.yaw = yaw; player.pitch = pitch;
        player.ground = floorHeightAt(x, z, 1e9);
        setRoom(roomAt(x, z));
        pumpArtQueue();
    },
    rooms_list() { return rooms.map(r => ({ n: r.name, x: (r.minX + r.maxX) / 2, z: (r.minZ + r.maxZ) / 2 })); },
    blocked(x, z) { const g0 = player.ground; player.ground = floorHeightAt(x, z, g0); const b = collide(x, z); player.ground = g0; return b; },
    ground(x, z) { return floorHeightAt(x, z, 1e9); },
    regions() {
        return regionState ? regionState.groups.map(g => ({ n: g.name.replace('region:', ''), vis: g.visible, kids: g.children.length })) : null;
    },
    pickNearestProduct() {
        let best = null, bd = 1e9;
        for (const it of interactables) {
            if (it.data.kind !== 'product') continue;
            const p = it.mesh.getWorldPosition(new THREE.Vector3());
            const d = p.distanceTo(player.pos);
            if (d < bd) { bd = d; best = it; }
        }
        if (best) pickUpBook(best);
        return best ? best.data.n : null;
    },
    pickNearestBook() {
        let best = null, bd = 1e9;
        for (const it of interactables) {
            if (it.data.kind !== 'book') continue;
            const p = it.mesh.getWorldPosition(new THREE.Vector3());
            const d = p.distanceTo(player.pos);
            if (d < bd) { bd = d; best = it; }
        }
        if (best) pickUpBook(best);
        return !!best;
    },
    start() {
        started = true;
        splash.classList.add('hide');
        pauseEl.classList.add('hide');
        hud.classList.remove('hide');
    },
};
