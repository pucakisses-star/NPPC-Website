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

/* ---------------------------------------------------------------- palette */
const INK = '#1e2122';
const PAPER = '#f4f1ea';
const GOLD = '#e4a524';
const CRIMSON = '#98002e';
const TEAL = '#2a6d81';
const ACCENTS = [GOLD, CRIMSON, TEAL, '#7c5cbf', '#b0592e', '#4e7d3a'];

/* ------------------------------------------------------------------ boot */
const canvas = document.getElementById('museum-canvas');
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
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
const texLoader = new THREE.TextureLoader();
function pbr(base, { repeat = [1, 1], color = true, normal = true, rough = true, ao = false, metal = false } = {}) {
    const out = {};
    const load = (suffix, isColor) => {
        const t = texLoader.load(`/images/museum/textures/${base}_${suffix}.jpg`);
        t.wrapS = t.wrapT = THREE.RepeatWrapping;
        t.repeat.set(repeat[0], repeat[1]);
        t.anisotropy = 8;
        if (isColor) t.colorSpace = THREE.SRGBColorSpace;
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
    t.anisotropy = 8;
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

function addCollider(minX, maxX, minZ, maxZ) { colliders.push({ minX, maxX, minZ, maxZ }); }
function addFloorZone(minX, maxX, minZ, maxZ, y) { floorZones.push({ minX, maxX, minZ, maxZ, y }); }
function floorHeightAt(x, z) {
    // Zones are non-overlapping tier platforms; return the containing zone's y.
    for (const f of floorZones) {
        if (x >= f.minX && x <= f.maxX && z >= f.minZ && z <= f.maxZ) return f.y;
    }
    return 0;
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
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
                const a = tex.image.width / tex.image.height, target = artW / artH;
                photoMat.map = tex; photoMat.emissiveMap = tex; photoMat.needsUpdate = true;
                if (a > target) photo.scale.set(1, target / a, 1);
                else photo.scale.set(a / target, 1, 1);
            }
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
function standee(item, x, z, ry) {
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
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
                boardMat.map = tex; boardMat.emissiveMap = tex; boardMat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = W / H;
                if (a > target) photo.scale.set(1, target / a, 1); else photo.scale.set(a / target, 1, 1);
            }
        });
    }
    addCollider(x - 0.45, x + 0.45, z - 0.3, z + 0.3);
    interactables.push({ mesh: photo, data: { kind: 'standee', ...item } });
    const plStand = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.78), MAT.frameBlack);
    plStand.position.set(W / 2 + 0.28, 0.39, 0.12); g.add(plStand);
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.4, 0.235),
        new THREE.MeshStandardMaterial({ map: placardTexture(item.n, item.l1, item.l2), roughness: 0.9 }));
    pl.position.set(W / 2 + 0.28, 0.82, 0.13); pl.rotation.x = -0.35; g.add(pl);
    return g;
}

/* Vitrine: table + glass case + document propped inside. */
function vitrine(item, x, z, ry = 0) {
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
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
                docMat.map = tex; docMat.emissiveMap = tex; docMat.needsUpdate = true;
                const a = tex.image.width / tex.image.height, target = 0.52 / 0.68;
                if (a > target) doc.scale.set(1, target / a, 1); else doc.scale.set(a / target, 1, 1);
            }
        });
    }
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.42, 0.245),
        new THREE.MeshStandardMaterial({ map: placardTexture(item.n, item.l1, item.l2), roughness: 0.9 }));
    pl.position.set(0, th + 0.02, td / 2 - 0.1); pl.rotation.x = -Math.PI / 2 + 0.35; g.add(pl);
    addCollider(x - tw / 2 - 0.1, x + tw / 2 + 0.1, z - td / 2 - 0.1, z + td / 2 + 0.1);
    interactables.push({ mesh: doc, data: { kind: 'doc', ...item } });
    interactables.push({ mesh: glass, data: { kind: 'doc', ...item } });
    return g;
}

function bench(x, z, ry = 0, fabricTop = false) {
    const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g);
    const seat = new THREE.Mesh(new THREE.BoxGeometry(1.7, 0.09, 0.48), fabricTop ? MAT.fabric : MAT.benchWood);
    seat.position.y = 0.46; seat.castShadow = true; seat.receiveShadow = true; g.add(seat);
    for (const sx of [-0.7, 0.7]) {
        const leg = new THREE.Mesh(new THREE.BoxGeometry(0.09, 0.42, 0.4), MAT.frameBlack);
        leg.position.set(sx, 0.21, 0); leg.castShadow = true; g.add(leg);
    }
    const c = Math.cos(ry), s = Math.sin(ry);
    const hw = 0.9 * Math.abs(c) + 0.3 * Math.abs(s), hd = 0.9 * Math.abs(s) + 0.3 * Math.abs(c);
    addCollider(x - hw, x + hw, z - hd, z + hd);
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
            url: item.img, pos: new THREE.Vector3(x, 1.8, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
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
    const tile = 0.34, gap = 0.02;
    const cols = Math.max(1, Math.floor(width / (tile + gap)));
    const rows = Math.max(1, Math.floor(height / (tile + gap)));
    const x0 = -((cols - 1) * (tile + gap)) / 2, y0 = -((rows - 1) * (tile + gap)) / 2;
    let idx = 0;
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const rec = items[idx % items.length]; idx++;
            if (!rec || !rec.img) continue;
            const m = new THREE.Mesh(new THREE.PlaneGeometry(tile, tile),
                new THREE.MeshStandardMaterial({ map: placeholderArt, roughness: 0.8, emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.34 }));
            m.position.set(x0 + c * (tile + gap), y0 + r * (tile + gap), 0);
            g.add(m);
            const worldPos = g.localToWorld(m.position.clone());
            const mat = m.material;
            artQueue.push({
                url: rec.img, pos: worldPos, apply: (tex) => {
                    tex.colorSpace = THREE.SRGBColorSpace;
                    mat.map = tex; mat.emissiveMap = tex; mat.needsUpdate = true;
                }, low: true,
            });
        }
    }
    g.updateMatrixWorld(true);
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
            url: item.img, pos: new THREE.Vector3(x, cy, z), apply: (tex) => {
                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
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
function plasterFigure(x, z, ry, { seated = false, scale = 1 } = {}) {
    const g = new THREE.Group(); g.position.set(x, 0, z); g.rotation.y = ry; worldGroup.add(g);
    const mat = new THREE.MeshStandardMaterial({ color: 0xdcdad2, roughness: 0.95, metalness: 0, envMapIntensity: 0.3 });
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
    const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
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

/* ---- Entrance rotunda: x[-9,9] z[6,20] ---- */
(function rotunda() {
    const Y = CEIL.rotunda;
    const refl = new Reflector(new THREE.PlaneGeometry(18, 14), {
        textureWidth: 1024, textureHeight: 1024, color: 0x8f8f8f, clipBias: 0.003,
    });
    refl.rotation.x = -Math.PI / 2; refl.position.set(0, 0.001, 13);
    worldGroup.add(refl); window.__reflector = refl;
    const marbleTop = floorRect(-9, 9, 6, 20, new THREE.MeshStandardMaterial({
        ...pbr('marble', { repeat: [5, 4] }), transparent: true, opacity: 0.72, roughness: 0.16, envMapIntensity: 0.85,
    }), 0.012);
    marbleTop.material.depthWrite = false;
    ceilRect(-9, 9, 6, 20, Y);
    const ring = new THREE.Mesh(new THREE.RingGeometry(1.7, 2.8, 48), new THREE.MeshBasicMaterial({ color: 0xfff2d8, side: THREE.DoubleSide }));
    ring.rotation.x = Math.PI / 2; ring.position.set(0, Y - 0.02, 13); worldGroup.add(ring);

    wallRun('z', 20, 6, 20, Y, {});                       // south (behind entry)
    wallRun('z', -9, 6, 20, Y, {});                       // west
    wallRun('z', 9, 6, 20, Y, {});                        // east
    wallRun('x', 6, -9, 9, Y, { doors: [{ at: 0, w: 3.2, h: 3.2 }] });  // north → spine

    wallPanel(titleTexture(), new THREE.Vector3(-4.9, 3.9, 6.16), new THREE.Vector3(0, 0, 1), 5.6, 2.9, { emissive: 0.42 });
    wallPanel(panelTexture('Welcome', 'A museum of\nAmerican dissent',
        'Everyone in this building was jailed, exiled, or detained in the United States for political reasons — for organizing a union, refusing a draft, demanding a vote, preaching a faith, or imagining their nation free. Ahead lies the Hall of Figures and, opening off it, a gallery for each movement. Beyond are the archive, a reading room, a theater, and a replica cell. Click any work to inspect it.'),
        new THREE.Vector3(4.9, 2.0, 6.16), new THREE.Vector3(0, 0, 1), 2.3, 3.0,
        { interact: { kind: 'panel', n: 'A museum of American dissent', l1: 'Welcome', d: 'Everyone in this building was jailed, exiled, or detained in the United States for political reasons. Ahead lies the Hall of Figures and a gallery for each movement. Click any work to inspect it and follow it to the full record.', u: '/database' } });

    brokenChain(0, 13);
    anchors.sculpt = [2.6, Y - 1.3, 14.4];
    anchors.sculptTarget = [0, 1.2, 13];

    const st = DATA.standees.slice(0, 6);
    [[-4.4, 16.5, 0.5], [4.4, 16.5, -0.5], [-6.6, 10.5, 0.7], [6.6, 10.5, -0.7], [-4.4, 8.6, 0.4], [4.4, 8.6, -0.4]]
        .forEach((s, i) => { if (st[i]) standee(st[i], s[0], s[1], s[2]); });

    ceilingLight(0, Y, 10, 2.4, 0.32); ceilingLight(0, Y, 16, 2.4, 0.32);
    rooms.push({
        name: 'Entrance Rotunda', minX: -9, maxX: 9, minZ: 6, maxZ: 20,
        rig: { key: { p: [0, Y - 0.5, 13], t: [0, 0, 13], i: 150, angle: 0.7, dist: 22 },
            fills: [[0, 4.8, 16, 34, 0xffe7c0], [-5, 3.6, 10, 24, 0xfff0d8], [5, 3.6, 10, 24, 0xfff0d8]] } });
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

(function spine() {
    const Y = CEIL.spine, X = 7;
    floorRect(-X, X, spineEnd, 6, MAT.hallFloor);
    cofferedCeiling(-X, X, spineEnd, 6, Y, { beam: TIMBER, ceil: MAT.ceiling, bay: 4, skylight: true });

    const eastDoors = galleryMeta.filter(m => m.sign > 0).map(m => ({ at: m.doorZ, w: 2.4, h: 2.8 }));
    const westDoors = galleryMeta.filter(m => m.sign < 0).map(m => ({ at: m.doorZ, w: 2.4, h: 2.8 }));
    wallRun('z', X, spineEnd, 6, Y, { doors: eastDoors });
    wallRun('z', -X, spineEnd, 6, Y, { doors: westDoors });
    wallRun('x', spineEnd, -X, X, Y, { doors: [{ at: 0, w: 3, h: 3 }] });   // → archive

    // colonnade + monoliths down the centre
    for (let z = 0; z >= spineEnd + 2; z -= BAND) {
        column(-5.4, z, Y); column(5.4, z, Y);
    }
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
    wallRun('x', zA, loX, hiX, Y, { mat: wallMat });
    wallRun('x', zB, loX, hiX, Y, { mat: wallMat });

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
    wallRun('x', zHi, xLo, xHi, Y, {});
    wallRun('z', xHi, zLo, zHi, Y, {});
    // west wall (x=8) built by archive with the door
    column(16, cz - 6, Y); column(16, cz + 6, Y);

    const reading = (DATA.reading || []).filter(r => r.img || r.file);
    const books = reading.filter(r => r.book), sheets = reading.filter(r => !r.book);
    const shelfWood = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1, 1] }), color: 0x6b4a2e, roughness: 0.6, envMapIntensity: 0.5 });
    const clothColors = [0x7a3b2e, 0x2e4a5c, 0x51402a, 0x3c5a3a, 0x5a2e3c, 0x2f3a55, 0x6e5a2f, 0x4a2f55];
    const fillerGeo = new THREE.BoxGeometry(1, 1, 1);
    let bi = 0;
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
            while (cx < usable / 2 - 0.12) {
                if (slot % 2 === 1 && bi < books.length && cx < usable / 2 - 0.3) {
                    const rec = books[bi++];
                    const coverMat = new THREE.MeshStandardMaterial({ map: placeholderArt, roughness: 0.8, emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.22 });
                    const clothMat = new THREE.MeshStandardMaterial({ color: clothColors[bi % clothColors.length], roughness: 0.75 });
                    const bw = 0.24, bh = 0.335, bd = 0.035;
                    const book = new THREE.Mesh(new THREE.BoxGeometry(bw, bh, bd), [clothMat, clothMat, clothMat, clothMat, coverMat, clothMat]);
                    book.position.set(cx + bw / 2, rowY + 0.02 + bh / 2, 0.045); book.rotation.y = Math.PI; book.castShadow = true; g.add(book);
                    if (rec.img) artQueue.push({ url: rec.img, pos: g.localToWorld(book.position.clone()), apply: t => { t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = 8; coverMat.map = t; coverMat.emissiveMap = t; coverMat.needsUpdate = true; } });
                    interactables.push({ mesh: book, data: { kind: 'book', gallery: 'Reading Room', ...rec } });
                    cx += bw + 0.06;
                } else {
                    const n = 3 + Math.floor(Math.random() * 5);
                    for (let k = 0; k < n && cx < usable / 2 - 0.06; k++) {
                        const sw = 0.03 + Math.random() * 0.035, sh = 0.26 + Math.random() * 0.075;
                        const spine2 = new THREE.Mesh(fillerGeo, new THREE.MeshStandardMaterial({ color: clothColors[Math.floor(Math.random() * clothColors.length)], roughness: 0.8 }));
                        spine2.scale.set(sw, sh, 0.22); spine2.position.set(cx + sw / 2, rowY + 0.02 + sh / 2, 0); g.add(spine2); cx += sw + 0.006;
                    }
                }
                slot++; cx += 0.02;
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
            if (rec.img) artQueue.push({ url: rec.img, pos: g.localToWorld(cov.position.clone()), apply: t => { t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = 8; covMat.map = t; covMat.emissiveMap = t; covMat.needsUpdate = true; const a = t.image.width / t.image.height, tg = 0.42 / 0.56; if (a > tg) cov.scale.set(1, tg / a, 1); else cov.scale.set(a / tg, 1, 1); } });
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
        name: 'Reading Room', minX: xLo, maxX: xHi, minZ: zLo, maxZ: zHi,
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

    // tiers: landing at the back (flush with archive, y=0) stepping DOWN to the pit
    const tiers = [
        { x0: -10.5, x1: -8, y: 0.0 },
        { x0: -13, x1: -10.5, y: -0.34 },
        { x0: -15.5, x1: -13, y: -0.68 },
        { x0: -18, x1: -15.5, y: -1.02 },
        { x0: -20.5, x1: -18, y: -1.36 },
    ];
    const carpet = new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [2, 3] }), color: 0x3a1520, roughness: 1, envMapIntensity: 0.1 });
    const riser = new THREE.MeshStandardMaterial({ color: 0x1a0e12, roughness: 0.9 });
    tiers.forEach(t => {
        const w = t.x1 - t.x0;
        const top = new THREE.Mesh(new THREE.BoxGeometry(w, 0.3, zHi - zLo - 0.4), carpet);
        top.position.set((t.x0 + t.x1) / 2, t.y - 0.15, cz); top.receiveShadow = true; worldGroup.add(top);
        // riser face toward the screen
        const rf = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.34, zHi - zLo - 0.4), riser);
        rf.position.set(t.x0, t.y - 0.32, cz); worldGroup.add(rf);
        addFloorZone(t.x0, t.x1, zLo, zHi, t.y);
        // seats: two blocks with a central aisle at cz
        const rowX = t.x0 + w * 0.42;
        const accent = 0xe0913a;
        for (let zz = zLo + 1.4; zz <= zHi - 1.4; zz += 0.78) {
            if (Math.abs(zz - cz) < 0.8) continue;      // central aisle
            cinemaSeat(rowX, t.y, zz, accent);
        }
        // block colliders (leave aisle open)
        addCollider(t.x0 + 0.1, t.x1 - 0.1, zLo + 1.1, cz - 0.7);
        addCollider(t.x0 + 0.1, t.x1 - 0.1, cz + 0.7, zHi - 1.1);
    });
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
}
let curRoom = null, prevRoom = null;
function roomAt(x, z) {
    for (const r of rooms) if (x >= r.minX && x <= r.maxX && z >= r.minZ && z <= r.maxZ) return r;
    return null;
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
    toast(r.name);
}

/* ----------------------------------------------------------------- player */
const player = {
    pos: new THREE.Vector3(0, 0, 17),
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
    if (e.code === 'Escape' && overlayOpen) { if (readerOpen) closeReader(); else closeOverlay(); }
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
document.getElementById('museum-enter').addEventListener('click', lock);
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
    for (const c of colliders) {
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
function updateHover() {
    raycaster.setFromCamera(new THREE.Vector2(0, 0), camera);
    const meshes = interactables.map(i => i.mesh);
    const hits = raycaster.intersectObjects(meshes, false);
    hovered = hits.length ? interactables.find(i => i.mesh === hits[0].object) : null;
    reticle.classList.toggle('on', !!hovered);
    hint.textContent = hovered ? (hovered.data.kind === 'panel' ? 'Click to read' : 'Click to inspect') : '';
}
function tryInspect() {
    if (!hovered || overlayOpen) return;
    const d = hovered.data;
    if (d.kind === 'book' && d.file) openReader(d);
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
let loading = 0;
const texCache = new Map();          // url → THREE.Texture (shared)
function pumpArtQueue() {
    if (!artQueue.length || loading >= 4) return;
    // portraits/frames (not low) load before the mosaic-tile swarm; then by distance
    artQueue.sort((a, b) =>
        (a.low ? 1 : 0) - (b.low ? 1 : 0) ||
        a.pos.distanceToSquared(player.pos) - b.pos.distanceToSquared(player.pos));
    while (loading < 4 && artQueue.length) {
        const job = artQueue.shift();
        if (texCache.has(job.url)) {
            const t = texCache.get(job.url);
            if (t.image) { job.apply(t); continue; }
        }
        loading++;
        texLoader.load(job.url,
            (tex) => { loading--; texCache.set(job.url, tex); job.apply(tex); },
            undefined,
            () => { loading--; });
    }
}
setInterval(pumpArtQueue, 250);      // decoupled from the render loop

/* ------------------------------------------------------------------- loop */
const clock = new THREE.Clock();
let frame = 0;
function tick() {
    requestAnimationFrame(tick);
    const dt = Math.min(clock.getDelta(), 0.05);
    frame++;
    updatePlayer(dt);      // camera always follows player state (input is gated inside)
    if (frame % 6 === 0) updateHover();
    if (frame % 20 === 0) {
        setRoom(roomAt(player.pos.x, player.pos.z));
        pumpArtQueue();
        // reflector only pays for itself while you can see the rotunda floor
        if (window.__reflector) window.__reflector.visible = player.pos.z > 4;
    }
    for (const s of slideshows) s.draw(dt);
    if (window.__dust) window.__dust.rotation.y = Math.sin(clock.elapsedTime * 0.05) * 0.02;
    if (cellLight) cellLight.intensity = 16 + Math.sin(clock.elapsedTime * 17) * 0.9 + Math.sin(clock.elapsedTime * 3.1) * 0.7;
    renderer.render(scene, camera);
}
setRoom(rooms[0]);
tick();

/* boot the first textures immediately (nearest ones) */
pumpArtQueue();

/* debug/test hook: teleport the player without pointer lock */
window.__museumDebug = {
    player, rooms,
    teleport(x, z, yaw = Math.PI, pitch = 0) {
        player.pos.set(x, 0, z);
        player.yaw = yaw; player.pitch = pitch;
        player.ground = floorHeightAt(x, z);
        setRoom(roomAt(x, z));
        pumpArtQueue();
    },
    rooms_list() { return rooms.map(r => ({ n: r.name, x: (r.minX + r.maxX) / 2, z: (r.minZ + r.maxZ) / 2 })); },
    start() {
        started = true;
        splash.classList.add('hide');
        pauseEl.classList.add('hide');
        hud.classList.remove('hide');
    },
};
