(function () {
  "use strict";

  var CONFIG = window.MIM || {};
  var START = new Date(CONFIG.marathon || "2027-01-31T07:00:00+01:00").getTime();
  var RACE_KM = 42.195;
  var DECIMAL = CONFIG.decimal || ",";
  var UI = CONFIG.ui || {};

  /* ---------- countdown ---------- */
  var q = function (k) { return document.querySelectorAll('[data-cd="' + k + '"]'); };
  var cells = {
    days: q("days"),
    hours: q("hours"),
    minutes: q("minutes"),
    seconds: q("seconds")
  };
  var live = document.querySelector("[data-cd-live]");
  var pad = function (n) { return String(n).padStart(2, "0"); };
  var setAll = function (list, value) {
    Array.prototype.forEach.call(list, function (el) { el.textContent = value; });
  };

  function tick() {
    var left = START - Date.now();
    if (left <= 0) {
      setAll(cells.days, "00");
      setAll(cells.hours, "00");
      setAll(cells.minutes, "00");
      setAll(cells.seconds, "00");
      if (live) live.textContent = UI.live || "Le marathon est lancé.";
      return;
    }
    var s = Math.floor(left / 1000);
    setAll(cells.days, pad(Math.floor(s / 86400)));
    setAll(cells.hours, pad(Math.floor((s % 86400) / 3600)));
    setAll(cells.minutes, pad(Math.floor((s % 3600) / 60)));
    setAll(cells.seconds, pad(s % 60));
  }
  tick();
  if (document.querySelector("[data-cd]")) setInterval(tick, 1000);

  /* ---------- reading progress as race distance ---------- */
  var bar = document.querySelector("[data-progress]");
  var dot = document.querySelector("[data-rail-dot]");
  var kmOut = document.querySelector("[data-km]");
  var ticking = false;

  function onScroll() {
    var max = document.documentElement.scrollHeight - window.innerHeight;
    var p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
    if (bar) bar.style.width = (p * 100).toFixed(2) + "%";
    if (dot) dot.style.top = (p * 100).toFixed(2) + "%";
    if (kmOut) {
      var km = p * RACE_KM;
      kmOut.textContent = km.toFixed(1).replace(".", DECIMAL);
    }
    ticking = false;
  }

  function requestScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(onScroll);
    }
  }
  onScroll();
  window.addEventListener("scroll", requestScroll, { passive: true });
  window.addEventListener("resize", requestScroll);

  /* ---------- hero background video ---------- */
  var hero = document.querySelector(".hero");
  var heroVideo = document.getElementById("hero-video");
  if (hero && heroVideo) {
    var revealVideo = function () { hero.classList.add("is-playing"); };
    var listenToPlayer = function () {
      try {
        heroVideo.contentWindow.postMessage(
          '{"event":"listening","id":"hero-video","channel":"widget"}',
          "https://www.youtube-nocookie.com"
        );
      } catch (e) {}
    };
    window.addEventListener("message", function (event) {
      if (event.origin !== "https://www.youtube-nocookie.com") return;
      var payload = event.data;
      if (typeof payload === "string") {
        try { payload = JSON.parse(payload); } catch (e) { return; }
      }
      if (!payload) return;
      var info = payload.info;
      if (info === 1 || (info && info.playerState === 1)) revealVideo();
    });
    heroVideo.addEventListener(
      "load",
      function () {
        listenToPlayer();
        window.setTimeout(listenToPlayer, 1200);
      },
      { once: true }
    );
    try {
      if (heroVideo.contentDocument === null) listenToPlayer();
    } catch (e) {
      listenToPlayer();
    }
  }

  /* ---------- mobile nav ---------- */
  var nav = document.getElementById("nav");
  var burger = document.querySelector(".burger");
  if (nav && burger) {
    burger.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      burger.setAttribute("aria-expanded", String(open));
    });
    nav.addEventListener("click", function (e) {
      if (e.target.closest("a")) {
        nav.classList.remove("is-open");
        burger.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* ---------- start board ---------- */
  var RACES = UI.races || {};

  if (CONFIG.races) {
    Object.keys(RACES).forEach(function (key) {
      if (CONFIG.races[key]) RACES[key].start = CONFIG.races[key];
    });
  }

  var detail = document.getElementById("race-detail");
  var rows = Array.prototype.slice.call(document.querySelectorAll(".board-row"));

  function fillRace(race) {
    if (!detail) return;
    detail.querySelector('[data-race-field="kicker"]').textContent = race.kicker;
    detail.querySelector('[data-race-field="name"]').textContent = race.name;
    detail.querySelector('[data-race-field="blurb"]').textContent = race.blurb;
    detail.querySelector('[data-race-field="distance"]').textContent = race.distance;
    detail.querySelector('[data-race-field="start"]').textContent = race.start;
    detail.querySelector('[data-race-field="limit"]').textContent = race.limit;
    detail.querySelector('[data-race-field="splits"]').textContent = race.splits;
    var ruleBox = detail.querySelector("[data-race-rule]");
    var ruleText = detail.querySelector('[data-race-field="rule"]');
    if (ruleBox && ruleText) {
      ruleText.textContent = race.rule || "";
      ruleBox.hidden = !race.rule;
    }
    var list = detail.querySelector('[data-race-field="notes"]');
    list.textContent = "";
    race.notes.forEach(function (txt) {
      var li = document.createElement("li");
      li.textContent = txt;
      list.appendChild(li);
    });
  }

  function selectRace(row, focus) {
    rows.forEach(function (r) {
      var on = r === row;
      r.classList.toggle("is-active", on);
      r.setAttribute("aria-selected", String(on));
      r.tabIndex = on ? 0 : -1;
    });
    fillRace(RACES[row.dataset.race]);
    if (detail) detail.setAttribute("aria-labelledby", row.id);
    if (focus) row.focus();
  }

  rows.forEach(function (row, i) {
    row.addEventListener("click", function () { selectRace(row); });
    row.addEventListener("keydown", function (e) {
      var dir =
        e.key === "ArrowDown" || e.key === "ArrowRight"
          ? 1
          : e.key === "ArrowUp" || e.key === "ArrowLeft"
          ? -1
          : 0;
      if (!dir) return;
      e.preventDefault();
      selectRace(rows[(i + dir + rows.length) % rows.length], true);
    });
  });
  if (rows.length) fillRace(RACES[rows[0].dataset.race]);

  /* ---------- prize switch ---------- */
  var switches = Array.prototype.slice.call(document.querySelectorAll(".switch-btn"));
  var panels = {
    marathon: document.querySelector('[data-panel="marathon"]'),
    semi: document.querySelector('[data-panel="semi"]')
  };
  switches.forEach(function (btn, i) {
    btn.addEventListener("click", function () {
      switches.forEach(function (b) {
        var on = b === btn;
        b.classList.toggle("is-active", on);
        b.setAttribute("aria-selected", String(on));
        b.tabIndex = on ? 0 : -1;
      });
      Object.keys(panels).forEach(function (k) {
        if (panels[k]) panels[k].hidden = k !== btn.dataset.table;
      });
    });
    btn.addEventListener("keydown", function (e) {
      var dir = e.key === "ArrowRight" ? 1 : e.key === "ArrowLeft" ? -1 : 0;
      if (!dir) return;
      e.preventDefault();
      var next = switches[(i + dir + switches.length) % switches.length];
      next.click();
      next.focus();
    });
  });

  /* ---------- programme filter ---------- */
  var filters = Array.prototype.slice.call(document.querySelectorAll(".filter"));
  var schedRows = Array.prototype.slice.call(document.querySelectorAll(".sched-row"));
  filters.forEach(function (btn) {
    btn.addEventListener("click", function () {
      filters.forEach(function (b) {
        var on = b === btn;
        b.classList.toggle("is-active", on);
        b.setAttribute("aria-pressed", String(on));
      });
      var day = btn.dataset.day;
      schedRows.forEach(function (row) {
        row.classList.toggle("is-hidden", day !== "all" && row.dataset.day !== day);
      });
    });
  });

  /* ---------- gallery lightbox ---------- */
  var galLinks = Array.prototype.slice.call(document.querySelectorAll(".gal-link"));
  var gal = document.getElementById("gal-lightbox");

  if (gal && galLinks.length && typeof gal.showModal === "function") {
    var galImg = gal.querySelector(".gal-lb-img");
    var galCounter = gal.querySelector("[data-gal-counter]");
    var galCaption = gal.querySelector("[data-gal-caption]");
    var galMeta = gal.querySelector("[data-gal-meta]");
    var counterTpl = gal.dataset.counter || "{current} / {total}";
    var current = 0;
    var opener = null;

    var galAt = function (i) {
      return galLinks[((i % galLinks.length) + galLinks.length) % galLinks.length];
    };

    function galRender(i) {
      current = ((i % galLinks.length) + galLinks.length) % galLinks.length;
      var link = galLinks[current];
      var thumb = link.querySelector("img");
      var meta = [link.dataset.edition];
      if (link.dataset.credit) meta.push((gal.dataset.creditLabel || "") + " " + link.dataset.credit);
      if (link.dataset.taken) meta.push((gal.dataset.dateLabel || "") + " " + link.dataset.taken);

      galImg.classList.add("is-loading");
      galImg.src = link.getAttribute("href");
      galImg.alt = thumb ? thumb.alt : "";
      galCaption.textContent = link.dataset.caption || "";
      galMeta.textContent = meta.filter(Boolean).join(" · ");
      galCounter.textContent = counterTpl
        .replace("{current}", String(current + 1))
        .replace("{total}", String(galLinks.length));

      /* Les deux voisines se chargent pendant la lecture de la photo courante. */
      [galAt(current - 1), galAt(current + 1)].forEach(function (neighbour) {
        var preload = new Image();
        preload.src = neighbour.getAttribute("href");
      });
    }

    galImg.addEventListener("load", function () {
      galImg.classList.remove("is-loading");
    });

    galLinks.forEach(function (link, i) {
      link.addEventListener("click", function (event) {
        event.preventDefault();
        opener = link;
        galRender(i);
        document.documentElement.classList.add("gal-open");
        gal.showModal();
      });
    });

    gal.addEventListener("close", function () {
      document.documentElement.classList.remove("gal-open");
      if (opener) opener.focus();
    });

    gal.addEventListener("click", function (event) {
      if (event.target === gal) gal.close();
    });

    gal.addEventListener("keydown", function (event) {
      /* En lecture droite-à-gauche, la photo précédente est à droite. */
      var rtl = document.documentElement.dir === "rtl";
      var step =
        event.key === "ArrowLeft" ? (rtl ? 1 : -1) : event.key === "ArrowRight" ? (rtl ? -1 : 1) : 0;
      if (step) {
        event.preventDefault();
        galRender(current + step);
        return;
      }
      if (event.key === "Home" || event.key === "End") {
        event.preventDefault();
        galRender(event.key === "Home" ? 0 : galLinks.length - 1);
      }
    });

    gal.querySelector("[data-gal-prev]").addEventListener("click", function () {
      galRender(current - 1);
    });
    gal.querySelector("[data-gal-next]").addEventListener("click", function () {
      galRender(current + 1);
    });
    gal.querySelector("[data-gal-close]").addEventListener("click", function () {
      gal.close();
    });
  }

  /* ---------- scroll reveal ---------- */
  var revealTargets = document.querySelectorAll(
    ".sec-head, .board, .parcours-copy, .parcours-fig, .schedule, .table-wrap, .bonus-row, .notes > *, .faq, .infos-intro, .contact > *, .index-row, .page-next, .home-close-inner, .page-toolbar, .gal-index-inner, .gal-album-head, .gal-item"
  );
  Array.prototype.forEach.call(revealTargets, function (el) { el.classList.add("reveal"); });

  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-in");
            io.unobserve(entry.target);
          }
        });
      },
      { rootMargin: "0px 0px -6% 0px", threshold: 0.06 }
    );
    Array.prototype.forEach.call(revealTargets, function (el) { io.observe(el); });
  } else {
    Array.prototype.forEach.call(revealTargets, function (el) { el.classList.add("is-in"); });
  }
})();
