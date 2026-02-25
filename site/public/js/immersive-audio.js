/* immersive-audio.js — Ambient sound for immersive scroll pages */
(function () {
  'use strict';

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  let ctx = null;
  let masterGain = null;
  let droneGain = null;
  let chimeGain = null;
  let reverbNode = null;
  let droneOscs = [];
  let chimeTimer = null;
  let isPlaying = false;

  /* ---- Reverb impulse (synthetic) ---- */
  function createReverb() {
    const len = ctx.sampleRate * 3;
    const buf = ctx.createBuffer(2, len, ctx.sampleRate);
    for (let ch = 0; ch < 2; ch++) {
      const data = buf.getChannelData(ch);
      for (let i = 0; i < len; i++) {
        data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 2.2);
      }
    }
    const conv = ctx.createConvolver();
    conv.buffer = buf;
    return conv;
  }

  /* ---- Deep gong/drone ---- */
  function startDrone() {
    droneGain = ctx.createGain();
    droneGain.gain.value = 0;
    droneGain.connect(masterGain);

    const reverbSend = ctx.createGain();
    reverbSend.gain.value = 0.3;
    reverbSend.connect(reverbNode);

    // Fundamental — deep C2 (65.41 Hz)
    const freqs = [65.41, 130.81, 98.0, 196.0];
    const gains = [0.35, 0.12, 0.08, 0.04];

    freqs.forEach((freq, i) => {
      const osc = ctx.createOscillator();
      osc.type = 'sine';
      osc.frequency.value = freq;

      // Subtle detuning for warmth
      if (i > 0) osc.detune.value = (Math.random() - 0.5) * 6;

      const g = ctx.createGain();
      g.gain.value = gains[i];

      // Slow LFO for living feel
      const lfo = ctx.createOscillator();
      lfo.type = 'sine';
      lfo.frequency.value = 0.05 + Math.random() * 0.08;
      const lfoGain = ctx.createGain();
      lfoGain.gain.value = gains[i] * 0.15;
      lfo.connect(lfoGain);
      lfoGain.connect(g.gain);
      lfo.start();

      osc.connect(g);
      g.connect(droneGain);
      g.connect(reverbSend);
      osc.start();

      droneOscs.push(osc, lfo);
    });

    // Low-pass filter for warmth
    const lpf = ctx.createBiquadFilter();
    lpf.type = 'lowpass';
    lpf.frequency.value = 180;
    lpf.Q.value = 0.7;

    // Reconnect through filter
    droneGain.disconnect();
    droneGain.connect(lpf);
    lpf.connect(masterGain);
    lpf.connect(reverbSend);

    // Gentle fade in over 4 seconds
    droneGain.gain.setValueAtTime(0, ctx.currentTime);
    droneGain.gain.linearRampToValueAtTime(1, ctx.currentTime + 4);
  }

  /* ---- High resonant chime ---- */
  const CHIME_NOTES = [
    1046.50, // C6
    1174.66, // D6
    1318.51, // E6
    1396.91, // F6
    783.99,  // G5
    880.00,  // A5
    987.77,  // B5
    1567.98, // G6
  ];

  function playChime() {
    if (!ctx || !isPlaying) return;

    const freq = CHIME_NOTES[Math.floor(Math.random() * CHIME_NOTES.length)];
    const now = ctx.currentTime;
    const vol = 0.04 + Math.random() * 0.04; // Very subtle

    // Main tone
    const osc1 = ctx.createOscillator();
    osc1.type = 'sine';
    osc1.frequency.value = freq;

    // Harmonic (octave + fifth above for shimmer)
    const osc2 = ctx.createOscillator();
    osc2.type = 'sine';
    osc2.frequency.value = freq * 2.98; // Near 3rd harmonic
    osc2.detune.value = (Math.random() - 0.5) * 8;

    // Third partial (subtle high shimmer)
    const osc3 = ctx.createOscillator();
    osc3.type = 'sine';
    osc3.frequency.value = freq * 5.04;

    // Gain envelope
    const env = ctx.createGain();
    env.gain.setValueAtTime(0, now);
    env.gain.linearRampToValueAtTime(vol, now + 0.008);
    env.gain.exponentialRampToValueAtTime(vol * 0.4, now + 0.8);
    env.gain.exponentialRampToValueAtTime(0.0001, now + 4);

    // Band-pass for bell resonance
    const bpf = ctx.createBiquadFilter();
    bpf.type = 'bandpass';
    bpf.frequency.value = freq;
    bpf.Q.value = 12;

    // Harmonic gains
    const g2 = ctx.createGain();
    g2.gain.value = 0.15;
    const g3 = ctx.createGain();
    g3.gain.value = 0.05;

    // Routing
    osc1.connect(bpf);
    osc2.connect(g2);
    g2.connect(bpf);
    osc3.connect(g3);
    g3.connect(bpf);
    bpf.connect(env);

    // Send to reverb + dry
    const dry = ctx.createGain();
    dry.gain.value = 0.3;
    const wet = ctx.createGain();
    wet.gain.value = 0.7;

    env.connect(dry);
    env.connect(wet);
    dry.connect(chimeGain);
    wet.connect(reverbNode);

    osc1.start(now);
    osc2.start(now);
    osc3.start(now);

    const stopTime = now + 5;
    osc1.stop(stopTime);
    osc2.stop(stopTime);
    osc3.stop(stopTime);
  }

  /* ---- Chime scheduling ---- */
  function scheduleNextChime() {
    if (!isPlaying) return;
    // Random interval between 6 and 18 seconds
    const delay = 6000 + Math.random() * 12000;
    chimeTimer = setTimeout(() => {
      playChime();
      // Occasionally play a second chime shortly after (duet effect)
      if (Math.random() < 0.3) {
        setTimeout(playChime, 800 + Math.random() * 1500);
      }
      scheduleNextChime();
    }, delay);
  }

  /* ---- Init / Toggle ---- */
  function init() {
    ctx = new (window.AudioContext || window.webkitAudioContext)();

    masterGain = ctx.createGain();
    masterGain.gain.value = 0.5;
    masterGain.connect(ctx.destination);

    chimeGain = ctx.createGain();
    chimeGain.gain.value = 1;
    chimeGain.connect(masterGain);

    reverbNode = createReverb();
    reverbNode.connect(masterGain);
  }

  function start() {
    if (!ctx) init();
    if (ctx.state === 'suspended') ctx.resume();

    isPlaying = true;
    startDrone();
    // First chime after 3-6 seconds
    chimeTimer = setTimeout(() => {
      playChime();
      scheduleNextChime();
    }, 3000 + Math.random() * 3000);
  }

  function stop() {
    isPlaying = false;
    if (chimeTimer) {
      clearTimeout(chimeTimer);
      chimeTimer = null;
    }
    if (droneGain) {
      const now = ctx.currentTime;
      droneGain.gain.setValueAtTime(droneGain.gain.value, now);
      droneGain.gain.linearRampToValueAtTime(0, now + 2);
    }
    if (chimeGain) {
      const now = ctx.currentTime;
      chimeGain.gain.setValueAtTime(chimeGain.gain.value, now);
      chimeGain.gain.linearRampToValueAtTime(0, now + 2);
    }
    // Stop oscillators after fade
    setTimeout(() => {
      droneOscs.forEach(o => { try { o.stop(); } catch (_) {} });
      droneOscs = [];
    }, 2500);
  }

  /* ---- UI: Sound toggle button ---- */
  const btn = document.querySelector('[data-immersive-sound]');
  if (!btn) return;

  function activate() {
    start();
    btn.classList.add('is-active');
    btn.setAttribute('aria-pressed', 'true');
  }

  function deactivate() {
    stop();
    btn.classList.remove('is-active');
    btn.setAttribute('aria-pressed', 'false');
  }

  btn.addEventListener('click', () => {
    if (isPlaying) deactivate(); else activate();
  });

  /* Auto-start from "Commencer" button click (valid user gesture) */
  window.addEventListener('immersive-start', () => {
    if (!isPlaying) activate();
  });

})();
