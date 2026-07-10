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
const interactables = [];       // {mesh, data}
const artQueue = [];            // progressive photo loading
const rooms = [];               // {name,minX,maxX,minZ,maxZ,rig}
const slideshows = [];          // animated canvas screens

function addCollider(minX, maxX, minZ, maxZ) { colliders.push({ minX, maxX, minZ, maxZ }); }
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
    for (const [a, b] of spans) {
        if (b - a < 0.05) continue;
        const len = b - a, mid = (a + b) / 2;
        if (axis === 'x') box(len, height, WALL_T, mat, mid, base + height / 2, fixed, { collide: true });
        else box(WALL_T, height, len, mat, fixed, base + height / 2, mid, { collide: true });
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
    artH = 1.15, frame = MAT.frameWood, placard = true, wash = true, light = true, gallery = ''
} = {}) {
    const g = new THREE.Group();
    g.position.copy(pos);
    g.lookAt(pos.clone().add(normal));
    worldGroup.add(g);

    const artW = artH * 0.8;                       // frame is fixed; photo letterboxes in
    const frameT = 0.07, frameD = 0.09, matPad = 0.09;

    // frame bars
    const ow = artW + matPad * 2 + frameT * 2, oh = artH + matPad * 2 + frameT * 2;
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

/* ================================================================= LAYOUT */
const CEIL = { rotunda: 6, hall: 4.2, gallery: 4, archive: 3.6, theater: 4.6, cell: 2.6 };

/* ---- Rotunda: x[-8,8] z[-8,8] ---- */
(function rotunda() {
    // marble floor + live reflection beneath a translucent marble sheet
    const refl = new Reflector(new THREE.PlaneGeometry(16, 16), {
        textureWidth: 1024, textureHeight: 1024, color: 0x9a9a9a, clipBias: 0.003,
    });
    refl.rotation.x = -Math.PI / 2; refl.position.set(0, 0.001, 0);
    worldGroup.add(refl);
    window.__reflector = refl;
    const marbleTop = floorRect(-8, 8, -8, 8, new THREE.MeshStandardMaterial({
        ...pbr('marble', { repeat: [5, 5] }), transparent: true, opacity: 0.72,
        roughness: 0.16, envMapIntensity: 0.8,
    }), 0.012);
    marbleTop.material.depthWrite = false;

    ceilRect(-8, 8, -8, 8, CEIL.rotunda);
    // oculus glow ring
    const ring = new THREE.Mesh(new THREE.RingGeometry(1.6, 2.6, 48),
        new THREE.MeshBasicMaterial({ color: 0xfff2d8, side: THREE.DoubleSide }));
    ring.rotation.x = Math.PI / 2; ring.position.set(0, CEIL.rotunda - 0.02, 0);
    worldGroup.add(ring);

    wallRun('x', -8, -8, 8, CEIL.rotunda, { doors: [{ at: 0, w: 2.6, h: 3 }] });      // north → hall
    wallRun('x', 8, -8, 8, CEIL.rotunda, {});                                          // south (title behind you)
    wallRun('z', -8, -8, 8, CEIL.rotunda, {});                                         // west
    wallRun('z', 8, -8, 8, CEIL.rotunda, {});                                          // east

    // title on north wall above the hall door
    wallPanel(titleTexture(), new THREE.Vector3(0, 4.5, -7.83), new THREE.Vector3(0, 0, 1), 7.4, 2.96, { emissive: 0.42 });
    // projection band on the south wall
    if (DATA.slides.length) {
        slideshowScreen(DATA.slides, null, new THREE.Vector3(0, 3.9, 7.83), new THREE.Vector3(0, 0, -1), 11, 3.1, { speed: 9 });
    }
    // welcome interpretive panels flanking the north door
    wallPanel(panelTexture('Welcome', 'A museum of\nAmerican dissent',
        'Every person in this building was jailed, exiled, or detained in the United States for political reasons — for organizing a union, refusing a draft, demanding a vote, preaching a faith, or imagining their nation free. Walk the timeline hall ahead; six galleries open from it. In the far rooms you will find the paper trail, a theater, and a replica of the cell where many of these stories end. Click any work to inspect it.'),
        new THREE.Vector3(-6.15, 1.9, -7.83), new THREE.Vector3(0, 0, 1), 2.1, 2.75,
        { interact: { kind: 'panel', n: 'A museum of American dissent', l1: 'Welcome', d: 'Every person in this building was jailed, exiled, or detained in the United States for political reasons — for organizing a union, refusing a draft, demanding a vote, preaching a faith, or imagining their nation free. Walk the timeline hall ahead; six galleries open from it. Click any work to inspect it, and follow its link to the full record in the NPPC database.' } });
    wallPanel(panelTexture('The Collection', 'One database,\nthousands of lives',
        `The National Political Prisoner Coalition documents ${DATA.stats.total || 'thousands of'} political prisoners across ${DATA.stats.eras || 'dozens of'} eras of American history — ${DATA.stats.inCustody || 'many'} of them still in custody today. The works hung here are drawn live from that database: every frame links to a full case record you can read, cite, and act on.`),
        new THREE.Vector3(6.15, 1.9, -7.83), new THREE.Vector3(0, 0, 1), 2.1, 2.75,
        { interact: { kind: 'panel', n: 'One database, thousands of lives', l1: 'The Collection', d: `The NPPC documents ${DATA.stats.total || 'thousands of'} political prisoners across American history — ${DATA.stats.inCustody || 'many'} still in custody today. Every frame in this museum links to a full record in the database.`, u: '/database' } });

    brokenChain(0, -1.2);

    // standees flanking the sculpture
    const st = DATA.standees.slice(0, 4);
    const spots = [[-3.4, 1.6, 0.6], [3.4, 1.6, -0.6], [-3.4, -3.8, 0.9], [3.4, -3.8, -0.9]];
    st.forEach((item, i) => { if (spots[i]) standee(item, spots[i][0], spots[i][1], spots[i][2]); });

    // a few faces on east/west walls
    const faces = DATA.faces.slice(0, 8);
    faces.forEach((f, i) => {
        const side = i < 4 ? -1 : 1;
        const zi = -5.2 + (i % 4) * 3.4;
        hangArt(f, new THREE.Vector3(side * 7.83, 1.62, zi), new THREE.Vector3(-side, 0, 0),
            { artH: 0.85, frame: MAT.frameBlack, gallery: 'Faces of the Database' });
    });

    ceilingLight(0, CEIL.rotunda, -4, 2.2, 0.3);
    ceilingLight(0, CEIL.rotunda, 4, 2.2, 0.3);
    rooms.push({
        name: 'Entrance Rotunda', minX: -8, maxX: 8, minZ: -8, maxZ: 8,
        rig: {
            key: { p: [0, 5.6, 0], t: [0, 0, -1.2], i: 260, angle: 0.62, dist: 18 },
            fills: [[0, 4.6, 5.6, 40, 0xffe7c0], [-5, 3.4, -5, 26, 0xfff0d8], [5, 3.4, -5, 26, 0xfff0d8]],
        }
    });
})();

/* ---- Timeline hall: x[-4,4] z[-32,-8] ---- */
(function hall() {
    floorRect(-4, 4, -32, -8, MAT.hallFloor);
    ceilRect(-4, 4, -32, -8, CEIL.hall);
    const doorE = [{ at: -12.75, w: 1.9, h: 2.6 }, { at: -20.25, w: 1.9, h: 2.6 }, { at: -27.75, w: 1.9, h: 2.6 }];
    const doorW = doorE;
    wallRun('z', 4, -32, -8, CEIL.hall, { doors: doorE });
    wallRun('z', -4, -32, -8, CEIL.hall, { doors: doorW });
    wallRun('x', -32, -4, 4, CEIL.hall, { doors: [{ at: 0, w: 2.4, h: 2.8 }] });   // to archive

    // timeline mural sliced across the east-wall segments
    if (DATA.timeline.length) {
        const tex = timelineTexture(DATA.timeline);
        const segs = [[-8, -11.8], [-13.7, -19.3], [-21.2, -26.8], [-28.7, -32]];
        const totalLen = segs.reduce((s, [a, b]) => s + (a - b), 0);
        let u = 0;
        for (const [a, b] of segs) {
            const len = a - b;
            const t2 = tex.clone(); t2.needsUpdate = true;
            t2.repeat.set(len / totalLen, 1); t2.offset.set(u, 0);
            u += len / totalLen;
            const m = new THREE.Mesh(new THREE.PlaneGeometry(len, 2.1),
                new THREE.MeshStandardMaterial({ map: t2, roughness: 0.92, emissive: 0xffffff, emissiveMap: t2, emissiveIntensity: 0.3 }));
            m.position.set(3.83, 1.8, (a + b) / 2);
            m.rotation.y = -Math.PI / 2;
            worldGroup.add(m);
            interactables.push({ mesh: m, data: { kind: 'panel', n: 'A Timeline of Political Imprisonment', l1: `${DATA.timeline[0]?.y ?? ''} — ${DATA.timeline[DATA.timeline.length - 1]?.y ?? ''}`, d: 'Key moments in the history of American political imprisonment, drawn from the NPPC timeline. Visit the full interactive timeline for every entry.', u: '/timeline' } });
        }
    }
    // faces corridor on the west-wall segments
    const faces = DATA.faces.slice(8);
    const segsW = [[-9.4, -11.6], [-14.2, -18.9], [-21.7, -26.4], [-29.1, -31.4]];
    let fi = 0;
    for (const [a, b] of segsW) {
        const len = Math.abs(a - b);
        const count = Math.max(1, Math.floor(len / 2.3));
        for (let k = 0; k < count && fi < faces.length; k++, fi++) {
            const z = a - (k + 0.5) * (len / count);
            hangArt(faces[fi], new THREE.Vector3(-3.83, 1.62, z), new THREE.Vector3(1, 0, 0),
                { artH: 0.8, frame: MAT.frameBlack, gallery: 'Faces of the Database' });
        }
    }
    for (let z = -11; z >= -29; z -= 6) { ceilingLight(0, CEIL.hall, z, 1.6, 0.24); }
    bench(0, -16.5, Math.PI / 2); bench(0, -24, Math.PI / 2);
    rooms.push({
        name: 'Timeline Hall', minX: -4, maxX: 4, minZ: -32, maxZ: -8,
        rig: {
            key: { p: [0, 3.9, -20], t: [3.4, 1.4, -20], i: 150, angle: 0.75, dist: 14 },
            fills: [[0, 3.6, -11, 26, 0xfff0d8], [0, 3.6, -20, 26, 0xfff0d8], [0, 3.6, -28.5, 26, 0xfff0d8]],
        }
    });
})();

/* ---- Six galleries: east wing x[4,16], west wing x[-16,-4], z[-9.5,-31] ---- */
(function galleries() {
    const parts = [-9.5, -16.66, -23.83, -31];
    const wings = [
        { sign: 1, minX: 4, maxX: 16 },   // east
        { sign: -1, minX: -16, maxX: -4 }, // west
    ];
    // shells
    for (const w of wings) {
        floorRect(w.minX, w.maxX, -31, -9.5, MAT.galleryFloor);
        ceilRect(w.minX, w.maxX, -31, -9.5, CEIL.gallery);
        wallRun('x', -9.5, w.minX, w.maxX, CEIL.gallery, {});
        wallRun('x', -31, w.minX, w.maxX, CEIL.gallery, {});
        wallRun('z', w.sign * 16, -31, -9.5, CEIL.gallery, {});
        wallRun('x', parts[1], w.minX, w.maxX, CEIL.gallery, {});
        wallRun('x', parts[2], w.minX, w.maxX, CEIL.gallery, {});
    }
    const galleryDefs = DATA.galleries.slice(0, 6);
    galleryDefs.forEach((gal, gi) => {
        const wing = wings[gi % 2];
        const row = Math.floor(gi / 2);            // 0,1,2 down the hall
        const zMin = parts[row + 1] + 0.15, zMax = parts[row] - 0.15;
        const zMid = (zMin + zMax) / 2;
        const inner = { minX: Math.min(wing.sign * 4.15, wing.sign * 15.85), maxX: Math.max(wing.sign * 4.15, wing.sign * 15.85) };
        const xInner = wing.sign > 0 ? 4.15 : -15.85;
        const xOuter = wing.sign > 0 ? 15.85 : -4.15;

        // interpretive panel just inside, on the hall-side wall next to the door
        const doorZ = [-12.75, -20.25, -27.75][row];
        wallPanel(panelTexture(`Gallery ${gi + 1}`, gal.title, gal.intro),
            new THREE.Vector3(wing.sign * 4.35, 1.85, doorZ + (wing.sign > 0 ? 2.6 : 2.6)),
            new THREE.Vector3(wing.sign, 0, 0), 1.9, 2.5,
            { interact: { kind: 'panel', n: gal.title, l1: `Gallery ${gi + 1}`, d: gal.intro } });

        // artworks: 3 north, 3 south, 3 outer, 1 near door wall
        const items = gal.items || [];
        const spotsList = [];
        for (let k = 0; k < 3; k++) spotsList.push({ pos: new THREE.Vector3(wing.sign * (6.6 + k * 3.1), 1.62, zMax - 0.17), n: new THREE.Vector3(0, 0, -1) });
        for (let k = 0; k < 3; k++) spotsList.push({ pos: new THREE.Vector3(wing.sign * (6.6 + k * 3.1), 1.62, zMin + 0.17), n: new THREE.Vector3(0, 0, 1) });
        for (let k = 0; k < 3; k++) spotsList.push({ pos: new THREE.Vector3(wing.sign * 15.68, 1.62, zMid + (k - 1) * 2.3), n: new THREE.Vector3(-wing.sign, 0, 0) });
        spotsList.push({ pos: new THREE.Vector3(wing.sign * 4.32, 1.62, zMin + 1.6), n: new THREE.Vector3(wing.sign, 0, 0) });
        const frameMat = [MAT.frameWood, MAT.frameBlack, MAT.frameGilt][gi % 3];
        items.slice(0, 10).forEach((item, k) => {
            if (spotsList[k]) hangArt(item, spotsList[k].pos, spotsList[k].n, { frame: frameMat, gallery: gal.title });
        });
        bench(wing.sign * 10, zMid, 0);
        ceilingLight(wing.sign * 10, CEIL.gallery, zMid, 1.8, 0.26);
        rooms.push({
            name: gal.title, minX: Math.min(4, wing.sign * 16), maxX: Math.max(4, wing.sign * 16) === 4 ? -4 : Math.max(4, wing.sign * 16), minZ: zMin - 0.3, maxZ: zMax + 0.3,
            rig: {
                key: { p: [wing.sign * 10, 3.7, zMid], t: [wing.sign * 10, 0.4, zMid + 1.8], i: 130, angle: 0.85, dist: 12 },
                fills: [[wing.sign * 7, 3.3, zMid - 1.6, 22, 0xfff0d8], [wing.sign * 13, 3.3, zMid + 1.6, 22, 0xfff0d8]],
            }
        });
        // fix wing min/max (computed clumsily above)
        const r = rooms[rooms.length - 1];
        r.minX = Math.min(wing.sign * 4, wing.sign * 16);
        r.maxX = Math.max(wing.sign * 4, wing.sign * 16);
    });
})();

/* ---- Archive room: x[-5,5] z[-42,-32] ---- */
(function archive() {
    floorRect(-5, 5, -42, -32, MAT.galleryFloor);
    ceilRect(-5, 5, -42, -32, CEIL.archive);
    wallRun('x', -42, -5, 5, CEIL.archive, { doors: [{ at: 0, w: 2.2, h: 2.6 }] });   // → reading room
    wallRun('x', -32, -5, -4, CEIL.archive, {});   // close the strips beside the hall door
    wallRun('x', -32, 4, 5, CEIL.archive, {});
    wallRun('z', 5, -42, -32, CEIL.archive, { doors: [{ at: -37, w: 1.14, h: 2.1 }] });   // steel door → cell
    wallRun('z', -5, -42, -32, CEIL.archive, { doors: [{ at: -37, w: 2.1, h: 2.6 }] });   // → theater
    // (north wall shared with hall already built)

    wallPanel(panelTexture('The Archive', 'The paper trail',
        'Movements leave paper: strike bulletins, defense-committee pamphlets, prison letters, petitions, flyers demanding freedom for people whose names would otherwise be lost. The documents in these cases are digitized from the NPPC archive — click any one to inspect it. Through the far door is the reading room, where every digitized publication is shelved and can be read cover to cover.'),
        new THREE.Vector3(-3.4, 1.85, -41.83), new THREE.Vector3(0, 0, 1), 2.2, 2.9,
        { interact: { kind: 'panel', n: 'The paper trail', l1: 'The Archive', d: 'Strike bulletins, defense-committee pamphlets, prison letters, and flyers from the NPPC digital archive. Click any case to inspect the document — or step into the reading room beyond and read the full scans.', u: '/archive' } });

    const docs = DATA.archive.slice(0, 10);
    const spots = [
        [-3.4, -34.2, 0.35], [-1.2, -34.2, 0], [1.2, -34.2, 0], [3.4, -34.2, -0.35],
        [-3.4, -39.8, 2.8], [-1.2, -39.8, Math.PI], [1.2, -39.8, Math.PI], [3.4, -39.8, -2.8],
        [-2.2, -37, Math.PI / 2], [2.2, -37, -Math.PI / 2],
    ];
    docs.forEach((d, i) => { if (spots[i]) vitrine(d, spots[i][0], spots[i][1], spots[i][2]); });
    ceilingLight(0, CEIL.archive, -35, 1.6, 0.24);
    ceilingLight(0, CEIL.archive, -39, 1.6, 0.24);
    rooms.push({
        name: 'The Archive', minX: -5, maxX: 5, minZ: -42, maxZ: -32,
        rig: {
            key: { p: [0, 3.3, -37], t: [0, 0.9, -37.2], i: 110, angle: 0.9, dist: 11 },
            fills: [[-2.5, 2.9, -35, 20, 0xffe9c8], [2.5, 2.9, -39, 20, 0xffe9c8]],
        }
    });
})();

/* ---- Theater: x[-17,-5] z[-42,-32] ---- */
(function theater() {
    floorRect(-17, -5, -42, -32, new THREE.MeshStandardMaterial({ ...pbr('woodfloor', { repeat: [4, 4], ao: true }), color: 0x6b6560, envMapIntensity: 0.3 }));
    ceilRect(-17, -5, -42, -32, CEIL.theater, new THREE.MeshStandardMaterial({ color: 0x1c1d21, roughness: 0.95 }));
    wallRun('x', -32, -17, -5, CEIL.theater, { mat: MAT.wallDark });
    wallRun('x', -42, -17, -5, CEIL.theater, { mat: MAT.wallDark });
    wallRun('z', -17, -42, -32, CEIL.theater, { mat: MAT.wallDark });
    // the archive built the shared x=-5 wall only up to its lower ceiling —
    // fill the band between the two ceiling heights
    wallRun('z', -5, -42, -32, CEIL.theater - CEIL.archive, { mat: MAT.wallDark, base: CEIL.archive });

    // screen on the west wall
    slideshowScreen(DATA.slides.length ? DATA.slides : [{ t: 'National Political Prisoner Coalition', img: '' }],
        DATA.video,
        new THREE.Vector3(-16.8, 2.2, -37), new THREE.Vector3(1, 0, 0), 7.6, 4.1, { speed: 7 });
    // screen surround
    box(0.1, 4.5, 8.2, MAT.frameBlack, -16.92, 2.2, -37, {});

    for (const [bx, bz] of [[-9.5, -35.2], [-9.5, -37], [-9.5, -38.8], [-12, -35.2], [-12, -37], [-12, -38.8]]) {
        bench(bx, bz, Math.PI / 2, true);
    }
    // projector + beam + dust
    const proj = box(0.5, 0.34, 0.42, MAT.frameBlack, -6.2, 2.6, -37, {});
    const beamGeo = new THREE.ConeGeometry(2.6, 10.4, 24, 1, true);
    const beam = new THREE.Mesh(beamGeo, new THREE.MeshBasicMaterial({
        color: 0xfff3da, transparent: true, opacity: 0.045, side: THREE.DoubleSide,
        blending: THREE.AdditiveBlending, depthWrite: false,
    }));
    beam.position.set(-11.4, 2.4, -37);
    beam.rotation.z = Math.PI / 2 - 0.04;
    worldGroup.add(beam);
    // dust motes confined to the projector beam cone
    const dustN = 140, dustPos = new Float32Array(dustN * 3);
    for (let i = 0; i < dustN; i++) {
        const t = Math.random();                       // 0 at projector → 1 at screen
        const rad = (0.12 + 2.1 * t) * Math.sqrt(Math.random());
        const ang = Math.random() * Math.PI * 2;
        dustPos[i * 3] = -6.4 - t * 10.2;
        dustPos[i * 3 + 1] = 2.55 - t * 0.35 + Math.sin(ang) * rad;
        dustPos[i * 3 + 2] = -37 + Math.cos(ang) * rad;
    }
    const dustGeo = new THREE.BufferGeometry();
    dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
    const dust = new THREE.Points(dustGeo, new THREE.PointsMaterial({
        color: 0xffeecd, size: 0.014, transparent: true, opacity: 0.5,
        blending: THREE.AdditiveBlending, depthWrite: false,
    }));
    worldGroup.add(dust);
    window.__dust = dust;

    wallPanel(panelTexture('Theater', 'Faces & places',
        'A rolling reel of the movements, prisons, and campaigns documented across the NPPC — the same landscapes and crowds that appear behind every topic page on this site. Take a seat.'),
        new THREE.Vector3(-5.17, 1.8, -34), new THREE.Vector3(-1, 0, 0), 1.7, 2.2,
        { interact: { kind: 'panel', n: 'Faces & places', l1: 'Theater', d: 'A rolling reel of the movements, prisons, and campaigns documented across the NPPC.' } });

    rooms.push({
        name: 'Theater', minX: -17, maxX: -5, minZ: -42, maxZ: -32,
        rig: {
            key: { p: [-11, 4.1, -37], t: [-11, 0.4, -37], i: 26, angle: 1.0, dist: 10 },
            fills: [[-6.5, 2.4, -37, 7, 0xffdca8], [-13, 1.8, -37, 9, 0xbcd0ff]],
        }
    });
})();

/* ---- Solitary cell: x[5,11] z[-40,-34] (entry x=5, z=-37) ---- */
(function cell() {
    // vestibule text outside the door (in archive room)
    wallPanel(panelTexture('Period Room', 'Solitary',
        'This room reproduces, at full scale, a segregation cell of the kind used across the federal system: roughly 2.4 by 3.4 metres, concrete on six sides, one bunk, one steel door with a food slot. People in this museum have spent years — in several cases decades — inside rooms like this one. Step in. Stay as long as you like; they could not leave.'),
        new THREE.Vector3(4.83, 1.8, -34.6), new THREE.Vector3(-1, 0, 0), 1.8, 2.4,
        { interact: { kind: 'panel', n: 'Solitary', l1: 'Period Room', d: 'A full-scale reproduction of a segregation cell: concrete on six sides, one bunk, one steel door with a food slot. On any given day roughly 120,000 people are held in restrictive housing in U.S. prisons and jails. Several people documented in this museum spent decades in rooms like this.' } });

    const y = CEIL.cell;
    floorRect(5.15, 10.85, -39.85, -34.15, MAT.concreteFloor);
    ceilRect(5.15, 10.85, -39.85, -34.15, y, MAT.concrete);
    wallRun('x', -34.15, 5, 11, y, { mat: MAT.concrete });
    wallRun('x', -39.85, 5, 11, y, { mat: MAT.concrete });
    wallRun('z', 10.85, -39.85, -34.15, y, { mat: MAT.concrete });
    // west wall (x=5) already built with door gap by archive; steel door swung
    // open nearly flat against the archive-side wall so the doorway stays clear
    box(0.06, 2.05, 1.05, MAT.metal, 4.82, 1.06, -38.24, { ry: -1.25 });
    addCollider(4.55, 5.1, -38.85, -37.7);
    const slot = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.05, 0.34), new THREE.MeshStandardMaterial({ color: 0x14161a }));
    slot.position.set(4.79, 1.02, -38.24); slot.rotation.y = -1.25; worldGroup.add(slot);

    // bunk
    box(0.9, 0.12, 2.0, MAT.metal, 10.3, 0.5, -38.6, { collide: true });
    box(0.86, 0.1, 1.96, new THREE.MeshStandardMaterial({ color: 0x7e8894, roughness: 0.9 }), 10.3, 0.61, -38.6, {});
    // steel sink/toilet hint
    box(0.42, 0.5, 0.42, MAT.metal, 10.5, 0.25, -34.8, { collide: true });
    // scratched tally marks — a placard inside
    const pl = new THREE.Mesh(new THREE.PlaneGeometry(0.5, 0.3),
        new THREE.MeshStandardMaterial({ map: placardTexture('22–24 hours a day', '~120,000 people in the U.S.', 'on any given day'), roughness: 0.95 }));
    pl.position.set(10.82, 1.5, -36.6); pl.rotation.y = -Math.PI / 2;
    worldGroup.add(pl);

    rooms.push({
        name: 'Solitary — Period Room', minX: 5, maxX: 11, minZ: -40, maxZ: -34,
        rig: {
            key: { p: [8, 2.45, -37], t: [8.4, 0, -37.6], i: 5, angle: 1.1, dist: 6 },
            fills: [[8, 2.2, -37, 1.2, 0xd7e4f2], [6.2, 1.3, -36, 0.6, 0x9fb4c8]],
        }
    });
    // bare bulb
    const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.05, 10, 10), new THREE.MeshBasicMaterial({ color: 0xf2f6ff }));
    bulb.position.set(8, y - 0.12, -37); worldGroup.add(bulb);
})();

/* ---- Reading room: x[-7,7] z[-56,-42], through the archive's south door ---- */
(function readingRoom() {
    const CH = 4.0;
    floorRect(-7, 7, -56, -42, MAT.galleryFloor);
    ceilRect(-7, 7, -56, -42, CH);
    // rug under the tables
    const rug = new THREE.Mesh(new THREE.PlaneGeometry(7.5, 5),
        new THREE.MeshStandardMaterial({ ...pbr('fabric', { repeat: [3, 2] }), color: 0x5c2434, roughness: 1, envMapIntensity: 0.15 }));
    rug.rotation.x = -Math.PI / 2; rug.position.set(0, 0.008, -49); rug.receiveShadow = true;
    worldGroup.add(rug);

    wallRun('x', -56, -7, 7, CH, {});
    wallRun('z', -7, -56, -42, CH, {});
    wallRun('z', 7, -56, -42, CH, {});
    wallRun('x', -42, -7, -5, CH, {});                                      // flanks beside the archive door
    wallRun('x', -42, 5, 7, CH, {});
    wallRun('x', -42, -5, 5, CH - CEIL.archive, { base: CEIL.archive });    // band above the lower archive ceiling

    const reading = (DATA.reading || []).filter(r => r.img || r.file);
    const books = reading.filter(r => r.book);
    const sheets = reading.filter(r => !r.book);

    // ---- shelf furniture -------------------------------------------------
    const shelfWood = new THREE.MeshStandardMaterial({ ...pbr('wood', { repeat: [1, 1] }), color: 0x6b4a2e, roughness: 0.6, envMapIntensity: 0.5 });
    const clothColors = [0x7a3b2e, 0x2e4a5c, 0x51402a, 0x3c5a3a, 0x5a2e3c, 0x2f3a55, 0x6e5a2f, 0x4a2f55];
    const fillerGeo = new THREE.BoxGeometry(1, 1, 1);
    let bi = 0;                                          // next interactable book

    function bookshelf(x, z, ry) {
        const g = new THREE.Group();
        g.position.set(x, 0, z); g.rotation.y = ry;
        worldGroup.add(g);
        g.updateMatrixWorld(true);          // localToWorld below runs pre-render
        const W = 2.4, H = 2.5, D = 0.34;
        const side = (sx) => {
            const m = new THREE.Mesh(new THREE.BoxGeometry(0.05, H, D), shelfWood);
            m.position.set(sx, H / 2, 0); m.castShadow = true; g.add(m);
        };
        side(-W / 2 + 0.025); side(W / 2 - 0.025);
        const top = new THREE.Mesh(new THREE.BoxGeometry(W, 0.06, D), shelfWood);
        top.position.set(0, H - 0.03, 0); top.castShadow = true; g.add(top);
        const back = new THREE.Mesh(new THREE.BoxGeometry(W - 0.06, H - 0.1, 0.03), new THREE.MeshStandardMaterial({ color: 0x2c2118, roughness: 0.9 }));
        back.position.set(0, H / 2, -D / 2 + 0.02); g.add(back);
        const plinthB = new THREE.Mesh(new THREE.BoxGeometry(W, 0.14, D), shelfWood);
        plinthB.position.set(0, 0.07, 0); g.add(plinthB);

        const rows = [0.5, 1.0, 1.5, 2.0];
        for (const ry2 of rows) {
            const board = new THREE.Mesh(new THREE.BoxGeometry(W - 0.08, 0.04, D - 0.04), shelfWood);
            board.position.set(0, ry2, 0); board.castShadow = true; g.add(board);
        }
        // fill rows: alternating face-out readable books and clusters of spines
        const usable = W - 0.16;
        for (const rowY of rows) {
            let cx = -usable / 2;
            let slot = 0;
            while (cx < usable / 2 - 0.12) {
                if (slot % 2 === 1 && bi < books.length && cx < usable / 2 - 0.3) {
                    const rec = books[bi++];
                    const coverMat = new THREE.MeshStandardMaterial({
                        map: placeholderArt, roughness: 0.8,
                        emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.22,
                    });
                    const clothMat = new THREE.MeshStandardMaterial({ color: clothColors[bi % clothColors.length], roughness: 0.75 });
                    const bw = 0.24, bh = 0.335, bd = 0.035;
                    const book = new THREE.Mesh(new THREE.BoxGeometry(bw, bh, bd),
                        [clothMat, clothMat, clothMat, clothMat, coverMat, clothMat]);
                    book.position.set(cx + bw / 2, rowY + 0.02 + bh / 2, 0.045);
                    book.rotation.y = Math.PI;              // cover faces the room
                    book.castShadow = true;
                    g.add(book);
                    // face-out books hang via a thin stand lip
                    if (rec.img) {
                        artQueue.push({
                            url: rec.img, pos: g.localToWorld(book.position.clone()), apply: (tex) => {
                                tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
                                coverMat.map = tex; coverMat.emissiveMap = tex; coverMat.needsUpdate = true;
                            }
                        });
                    }
                    interactables.push({ mesh: book, data: { kind: 'book', gallery: 'Reading Room', ...rec } });
                    cx += bw + 0.06;
                } else {
                    // cluster of filler spines
                    const n = 3 + Math.floor(Math.random() * 5);
                    for (let k = 0; k < n && cx < usable / 2 - 0.06; k++) {
                        const sw = 0.03 + Math.random() * 0.035;
                        const sh = 0.26 + Math.random() * 0.075;
                        const spine = new THREE.Mesh(fillerGeo,
                            new THREE.MeshStandardMaterial({ color: clothColors[Math.floor(Math.random() * clothColors.length)], roughness: 0.8 }));
                        spine.scale.set(sw, sh, 0.22);
                        spine.position.set(cx + sw / 2, rowY + 0.02 + sh / 2, 0);
                        g.add(spine);
                        cx += sw + 0.006;
                    }
                }
                slot++;
                cx += 0.02;
            }
        }
        // collider (axis-aligned; shelves sit flush on walls)
        const c = Math.cos(ry), s = Math.sin(ry);
        const hw = (W / 2) * Math.abs(c) + (D / 2 + 0.1) * Math.abs(s);
        const hd = (W / 2) * Math.abs(s) + (D / 2 + 0.1) * Math.abs(c);
        addCollider(x - hw, x + hw, z - hd, z + hd);
    }

    // shelves: three on the south wall, two each on east/west
    bookshelf(-2.6, -55.78, 0); bookshelf(0, -55.78, 0); bookshelf(2.6, -55.78, 0);
    bookshelf(-6.78, -46.5, Math.PI / 2); bookshelf(-6.78, -51.5, Math.PI / 2);
    bookshelf(6.78, -46.5, -Math.PI / 2); bookshelf(6.78, -51.5, -Math.PI / 2);

    // ---- zine racks: face-out flyers flanking the door -------------------
    function zineRack(x, z, ry, items) {
        if (!items.length) return;
        const g = new THREE.Group();
        g.position.set(x, 0, z); g.rotation.y = ry;
        worldGroup.add(g);
        g.updateMatrixWorld(true);
        const frame = new THREE.Mesh(new THREE.BoxGeometry(2.5, 1.8, 0.05), MAT.benchWood);
        frame.position.set(0, 1.55, 0); g.add(frame);
        items.slice(0, 8).forEach((rec, i) => {
            const col = i % 4, row = Math.floor(i / 4);
            const covMat = new THREE.MeshStandardMaterial({
                map: placeholderArt, roughness: 0.85,
                emissive: 0xffffff, emissiveMap: placeholderArt, emissiveIntensity: 0.26,
            });
            const cov = new THREE.Mesh(new THREE.PlaneGeometry(0.42, 0.56), covMat);
            cov.position.set(-0.93 + col * 0.62, 2.05 - row * 0.85, 0.05);
            cov.rotation.x = -0.09;
            g.add(cov);
            const lip = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.025, 0.07), MAT.frameBlack);
            lip.position.set(-0.93 + col * 0.62, 1.74 - row * 0.85, 0.05); g.add(lip);
            if (rec.img) {
                artQueue.push({
                    url: rec.img, pos: g.localToWorld(cov.position.clone()), apply: (tex) => {
                        tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
                        covMat.map = tex; covMat.emissiveMap = tex; covMat.needsUpdate = true;
                        const a = tex.image.width / tex.image.height, target = 0.42 / 0.56;
                        if (a > target) cov.scale.set(1, target / a, 1); else cov.scale.set(a / target, 1, 1);
                    }
                });
            }
            interactables.push({ mesh: cov, data: { kind: 'book', gallery: 'Reading Room', ...rec } });
        });
    }
    zineRack(-3.2, -42.19, Math.PI, sheets);
    zineRack(3.2, -42.19, Math.PI, sheets.slice(8));

    // ---- reading tables with banker's lamps ------------------------------
    function readingTable(x, z) {
        const g = new THREE.Group(); g.position.set(x, 0, z); worldGroup.add(g);
        const top = new THREE.Mesh(new THREE.BoxGeometry(1.9, 0.055, 1.0), MAT.benchWood);
        top.position.y = 0.76; top.castShadow = true; top.receiveShadow = true; g.add(top);
        for (const [lx, lz] of [[-0.85, -0.4], [0.85, -0.4], [-0.85, 0.4], [0.85, 0.4]]) {
            const leg = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.74, 0.07), shelfWood);
            leg.position.set(lx, 0.37, lz); g.add(leg);
        }
        // banker's lamp
        const base = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.08, 0.03, 16), MAT.brass);
        base.position.set(0, 0.8, -0.25); g.add(base);
        const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, 0.26, 8), MAT.brass);
        stem.position.set(0, 0.94, -0.25); g.add(stem);
        const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.11, 0.11, 12, 1, true, 0, Math.PI),
            new THREE.MeshStandardMaterial({ color: 0x1f4d38, roughness: 0.35, metalness: 0.4, side: THREE.DoubleSide, envMapIntensity: 0.9 }));
        shade.position.set(0, 1.07, -0.25); shade.rotation.y = Math.PI / 2; shade.rotation.z = Math.PI / 2; g.add(shade);
        const glow = new THREE.Mesh(new THREE.CylinderGeometry(0.075, 0.095, 0.09, 10, 1, true, 0, Math.PI),
            new THREE.MeshBasicMaterial({ color: 0xffe9b8, side: THREE.DoubleSide }));
        glow.position.set(0, 1.06, -0.245); glow.rotation.y = Math.PI / 2; glow.rotation.z = Math.PI / 2; g.add(glow);
        // pool of lamp light on the table
        const pool = new THREE.Mesh(new THREE.PlaneGeometry(1.5, 1.0),
            new THREE.MeshBasicMaterial({ map: WASH_TEX, transparent: true, opacity: 0.34, blending: THREE.AdditiveBlending, depthWrite: false }));
        pool.rotation.x = -Math.PI / 2; pool.position.set(0, 0.792, -0.1); g.add(pool);
        // open book on the table
        for (const s of [-1, 1]) {
            const page = new THREE.Mesh(new THREE.PlaneGeometry(0.21, 0.3),
                new THREE.MeshStandardMaterial({ color: 0xf7f3e6, roughness: 0.95 }));
            page.position.set(s * 0.105, 0.795, 0.12);
            page.rotation.x = -Math.PI / 2; page.rotation.y = s * 0.09;
            g.add(page);
        }
        addCollider(x - 1.05, x + 1.05, z - 0.6, z + 0.6);
        bench(x, z + 1.05, 0);
        bench(x, z - 1.05, Math.PI);
    }
    readingTable(-2.6, -48.8);
    readingTable(2.6, -48.8);

    ceilingLight(-2.6, CH, -48.8, 1.8, 0.26);
    ceilingLight(2.6, CH, -48.8, 1.8, 0.26);

    wallPanel(panelTexture('Reading Room', 'Take a book\nfrom the shelf',
        'Everything digitized in the NPPC archive is shelved here — zines from inside, defense-committee pamphlets, prisoner-support periodicals, and the flyers racked by the door. Click any book or cover to pick it up and read the full scan, page by page. Long sentences were survived one page at a time; some of these were written that way too.'),
        new THREE.Vector3(-5.85, 1.85, -42.16), new THREE.Vector3(0, 0, -1), 1.9, 2.5,
        { interact: { kind: 'panel', n: 'Take a book from the shelf', l1: 'Reading Room', d: 'Everything digitized in the NPPC archive is shelved in this room. Click any book or racked cover to read the full scan — or browse the whole collection in the archive.', u: '/archive' } });

    rooms.push({
        name: 'Reading Room', minX: -7, maxX: 7, minZ: -56, maxZ: -42,
        rig: {
            key: { p: [0, 3.5, -49], t: [0, 0.5, -49.4], i: 105, angle: 0.95, dist: 15 },
            fills: [[-2.6, 2.3, -48.8, 22, 0xffdfae], [2.6, 2.3, -48.8, 22, 0xffdfae], [0, 2.6, -43.6, 20, 0xffe6c0]],
        }
    });
})();

/* --------------------------------------------------------------- lighting */
scene.add(new THREE.HemisphereLight(0xe8eeff, 0x3a352e, 0.32));

const cellLight = new THREE.PointLight(0xdfe8f5, 16, 7, 1.8);
cellLight.position.set(8, CEIL.cell - 0.18, -37);
scene.add(cellLight);

const theaterBeamLight = new THREE.SpotLight(0xfff0d5, 70, 13, 0.5, 0.55, 1.4);
theaterBeamLight.position.set(-6.3, 2.6, -37);
theaterBeamLight.target.position.set(-16.6, 2.2, -37);
scene.add(theaterBeamLight, theaterBeamLight.target);

const sculptLight = new THREE.SpotLight(0xffe9c2, 800, 12, 0.42, 0.5, 1.9);
sculptLight.position.set(2.6, 5.7, 1.4);
sculptLight.target.position.set(0, 1.2, -1.2);
sculptLight.castShadow = true;
sculptLight.shadow.mapSize.set(1024, 1024);
sculptLight.shadow.bias = -0.0004;
scene.add(sculptLight, sculptLight.target);

/* Reposition-only pool → shader program count stays constant. */
const keyA = new THREE.SpotLight(0xfff1da, 900, 20, 0.7, 0.55, 1.8);
keyA.castShadow = true; keyA.shadow.mapSize.set(1024, 1024); keyA.shadow.bias = -0.0004;
const keyB = keyA.clone();
scene.add(keyA, keyA.target, keyB, keyB.target);
const fillPool = [];
for (let i = 0; i < 3; i++) {
    const p = new THREE.PointLight(0xfff0d8, 0, 10, 2);
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
    pos: new THREE.Vector3(0, 0, 5.6),
    yaw: 0,                  // rotateY(0) → camera looks -z, into the museum
    pitch: 0,
    vel: new THREE.Vector3(),
    eye: 1.65,
    bob: 0,
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
    camera.position.set(player.pos.x, player.eye + bobY, player.pos.z);
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
    artQueue.sort((a, b) => a.pos.distanceToSquared(player.pos) - b.pos.distanceToSquared(player.pos));
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
        if (window.__reflector) window.__reflector.visible = player.pos.z > -13;
    }
    for (const s of slideshows) s.draw(dt);
    if (window.__dust) window.__dust.rotation.y = Math.sin(clock.elapsedTime * 0.05) * 0.02;
    cellLight.intensity = 16 + Math.sin(clock.elapsedTime * 17) * 0.9 + Math.sin(clock.elapsedTime * 3.1) * 0.7;
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
        setRoom(roomAt(x, z));
        pumpArtQueue();
    },
    start() {
        started = true;
        splash.classList.add('hide');
        pauseEl.classList.add('hide');
        hud.classList.remove('hide');
    },
};
