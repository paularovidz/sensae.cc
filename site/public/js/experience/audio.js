/**
 * Audio — Bell synthesis + drone via Web Audio API
 */

// Note frequencies (C4 to E5)
const NOTES = [
    261.63, // C4  — Do
    293.66, // D4  — Ré
    329.63, // E4  — Mi
    392.00, // G4  — Sol
    440.00, // A4  — La
    523.25, // C5  — Do
    587.33, // D5  — Ré
    659.25, // E5  — Mi
];

// Full keyboard mapping → note index (all letters play a note)
const KEY_MAP = {
    // Primary row (AZERTY melody)
    a: 0, z: 1, e: 2, r: 3, t: 4, y: 5, u: 6, i: 7,
    // Other keys mapped to notes (wrapping around)
    q: 0, s: 1, d: 2, f: 3, g: 4, h: 5, j: 6, k: 7,
    w: 0, x: 1, c: 2, v: 3, b: 4, n: 5, m: 6, l: 7,
    o: 0, p: 1,
};

export function createAudio() {
    let ctx = null;
    let reverb = null;
    let master = null;
    let droneOsc = null;
    let droneGain = null;
    let droneActive = false;

    function ensureCtx() {
        if (ctx) return ctx;
        ctx = new (window.AudioContext || window.webkitAudioContext)();
        master = ctx.createGain();
        master.gain.value = 0.6;
        master.connect(ctx.destination);
        // Build synthetic impulse response for reverb
        reverb = createReverb(ctx);
        reverb.connect(master);
        return ctx;
    }

    function resume() {
        if (ctx && ctx.state === 'suspended') ctx.resume();
    }

    /**
     * Play a bell note.
     * @param {number} index — note index (0-7)
     */
    function playBell(index) {
        ensureCtx();
        resume();

        const freq = NOTES[index] || NOTES[0];
        const now = ctx.currentTime;

        // Envelope gain
        const env = ctx.createGain();
        env.gain.setValueAtTime(0, now);
        env.gain.linearRampToValueAtTime(0.35, now + 0.005); // attack 5ms
        env.gain.linearRampToValueAtTime(0.12, now + 0.15);  // decay → sustain
        env.gain.exponentialRampToValueAtTime(0.001, now + 2.5); // release 2.5s

        // Bandpass filter for bell resonance
        const filter = ctx.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.value = freq * 2;
        filter.Q.value = 8;

        // 3 oscillators: fundamental + harmonics
        const oscs = [
            { f: freq, g: 0.5 },
            { f: freq * 2.02, g: 0.2 },  // slight detune for shimmer
            { f: freq * 3.01, g: 0.08 },
        ];

        const nodes = oscs.map(({ f, g }) => {
            const osc = ctx.createOscillator();
            osc.type = 'sine';
            osc.frequency.value = f;

            const gain = ctx.createGain();
            gain.gain.value = g;

            osc.connect(gain);
            gain.connect(filter);
            osc.start(now);
            osc.stop(now + 3);
            return osc;
        });

        filter.connect(env);
        env.connect(reverb); // → reverb → master

        // Also send dry signal
        const dry = ctx.createGain();
        dry.gain.value = 0.3;
        env.connect(dry);
        dry.connect(master);

        // Cleanup
        setTimeout(() => {
            nodes.forEach(o => { try { o.disconnect(); } catch {} });
            env.disconnect();
            filter.disconnect();
            dry.disconnect();
        }, 3500);
    }

    /**
     * Start a low drone — call once, control with setDroneVolume
     */
    function startDrone() {
        if (droneActive) return;
        ensureCtx();
        resume();
        droneActive = true;

        droneOsc = ctx.createOscillator();
        droneOsc.type = 'sine';
        droneOsc.frequency.value = 65.41; // C2

        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 120;

        droneGain = ctx.createGain();
        droneGain.gain.value = 0;

        droneOsc.connect(filter);
        filter.connect(droneGain);
        droneGain.connect(master);
        droneOsc.start();

        // Fade in
        droneGain.gain.linearRampToValueAtTime(0.08, ctx.currentTime + 2);
    }

    function setDroneVolume(vol, duration = 1) {
        if (!droneGain || !ctx) return;
        // Cancel any in-progress ramp to avoid volume jumps
        droneGain.gain.cancelScheduledValues(ctx.currentTime);
        droneGain.gain.setValueAtTime(droneGain.gain.value, ctx.currentTime);
        droneGain.gain.linearRampToValueAtTime(vol, ctx.currentTime + duration);
    }

    function stopDrone(fadeOut = 3) {
        if (!droneActive || !droneGain || !ctx) return;
        droneGain.gain.cancelScheduledValues(ctx.currentTime);
        droneGain.gain.setValueAtTime(droneGain.gain.value, ctx.currentTime);
        droneGain.gain.linearRampToValueAtTime(0, ctx.currentTime + fadeOut);
        setTimeout(() => {
            try { droneOsc.stop(); droneOsc.disconnect(); } catch {}
            droneActive = false;
        }, fadeOut * 1000 + 200);
    }

    /**
     * Play a soft chime. Two variants:
     * - 'low' (default): marks inhale→exhale transition (G5, warmer)
     * - 'high': marks cycle boundary / exhale→inhale (C6, brighter)
     */
    function playChime(variant = 'high') {
        ensureCtx();
        resume();

        const freq = variant === 'high' ? 1046.5 : 784.0; // C6 or G5
        const vol = variant === 'high' ? 0.12 : 0.08;
        const now = ctx.currentTime;

        const env = ctx.createGain();
        env.gain.setValueAtTime(0, now);
        env.gain.linearRampToValueAtTime(vol, now + 0.01);
        env.gain.linearRampToValueAtTime(vol * 0.3, now + 0.3);
        env.gain.exponentialRampToValueAtTime(0.001, now + 3);

        const filter = ctx.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.value = freq * 1.5;
        filter.Q.value = 12;

        const osc1 = ctx.createOscillator();
        osc1.type = 'sine';
        osc1.frequency.value = freq;
        const g1 = ctx.createGain();
        g1.gain.value = 0.3;
        osc1.connect(g1);
        g1.connect(filter);

        const osc2 = ctx.createOscillator();
        osc2.type = 'sine';
        osc2.frequency.value = freq * 2.01;
        const g2 = ctx.createGain();
        g2.gain.value = 0.08;
        osc2.connect(g2);
        g2.connect(filter);

        filter.connect(env);
        env.connect(reverb);

        osc1.start(now);
        osc2.start(now);
        osc1.stop(now + 3.5);
        osc2.stop(now + 3.5);

        setTimeout(() => {
            try { osc1.disconnect(); osc2.disconnect(); env.disconnect(); filter.disconnect(); } catch {}
        }, 4000);
    }

    return {
        get ctx() { return ctx; },
        get keyMap() { return KEY_MAP; },
        get noteCount() { return NOTES.length; },
        ensureCtx,
        resume,
        playBell,
        playChime,
        startDrone,
        setDroneVolume,
        stopDrone,
    };
}

/**
 * Create a synthetic reverb impulse response (~2s).
 */
function createReverb(ctx) {
    const convolver = ctx.createConvolver();
    const rate = ctx.sampleRate;
    const length = rate * 2;
    const impulse = ctx.createBuffer(2, length, rate);

    for (let ch = 0; ch < 2; ch++) {
        const data = impulse.getChannelData(ch);
        for (let i = 0; i < length; i++) {
            data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / length, 2.5);
        }
    }

    convolver.buffer = impulse;
    return convolver;
}
