/* =========================================================
   app.js (core helpers) - Hotel Booking / cualquier proyecto
   - Fetch JSON seguro (maneja HTML/500 => "No JSON")
   - Helpers: esc, money, qs, qsa, formToObject, debounce
   - Toasts Bootstrap (si está cargado bootstrap.bundle)
   ========================================================= */

(function(){
  "use strict";

  // ---------- DOM Helpers ----------
  window.qs  = (sel, root=document) => root.querySelector(sel);
  window.qsa = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  // ---------- Escape HTML ----------
  window.esc = function(s){
    return (s ?? "").toString().replace(/[&<>"']/g, m => ({
      "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
    }[m]));
  };

  // ---------- Money (cents -> $0.00) ----------
  window.money = function(cents){
    return "$" + (Number(cents || 0) / 100).toFixed(2);
  };

  // ---------- Date helpers ----------
  window.todayISO = function(){
    const d = new Date();
    const pad = (n)=> String(n).padStart(2,"0");
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  };

  // ---------- Debounce ----------
  window.debounce = function(fn, ms=300){
    let t;
    return (...args)=>{
      clearTimeout(t);
      t = setTimeout(()=>fn(...args), ms);
    };
  };

  // ---------- Form helpers ----------
  window.formToObject = function(form){
    const fd = new FormData(form);
    const obj = {};
    for(const [k,v] of fd.entries()){
      if(obj[k] !== undefined){
        if(!Array.isArray(obj[k])) obj[k] = [obj[k]];
        obj[k].push(v);
      }else obj[k] = v;
    }
    return obj;
  };

  window.objectToFormData = function(obj){
    const fd = new FormData();
    Object.entries(obj || {}).forEach(([k,v])=>{
      if(Array.isArray(v)) v.forEach(x=>fd.append(k, x));
      else if(v !== undefined && v !== null) fd.append(k, v);
    });
    return fd;
  };

  // ---------- URL helpers ----------
  window.buildQuery = function(params){
    const u = new URLSearchParams();
    Object.entries(params||{}).forEach(([k,v])=>{
      if(v === undefined || v === null) return;
      u.set(k, String(v));
    });
    return u.toString();
  };

  // ---------- Core Fetch JSON ----------
  async function readTextSafe(res){
    try { return await res.text(); } catch { return ""; }
  }

  function tryParseJSON(text){
    try { return JSON.parse(text); } catch { return null; }
  }

  /**
   * apiGet(url, options?)
   * Returns: {ok:boolean, ...data} OR {ok:false, error, status, raw}
   */
  window.apiGet = async function(url, options={}){
    try{
      const res = await fetch(url, {
        method: "GET",
        credentials: "same-origin",
        cache: "no-store",
        ...options
      });

      const raw = await readTextSafe(res);
      const json = tryParseJSON(raw);

      if(!json){
        return {
          ok: false,
          error: "No JSON",
          status: res.status,
          raw
        };
      }

      // si el backend ya manda ok=false, lo respetamos
      if(json.ok === undefined){
        // si no trae ok, lo inferimos por HTTP status
        json.ok = res.ok;
      }

      // si HTTP falló pero el json no trae error, agregamos uno
      if(!res.ok && !json.error){
        json.error = `HTTP ${res.status}`;
      }

      return json;
    }catch(err){
      return { ok:false, error: err?.message || "Network error" };
    }
  };

  /**
   * apiPost(url, formData|object, options?)
   */
  window.apiPost = async function(url, data, options={}){
    try{
      const body = (data instanceof FormData) ? data : objectToFormData(data);

      const res = await fetch(url, {
        method: "POST",
        body,
        credentials: "same-origin",
        cache: "no-store",
        ...options
      });

      const raw = await readTextSafe(res);
      const json = tryParseJSON(raw);

      if(!json){
        return {
          ok: false,
          error: "No JSON",
          status: res.status,
          raw
        };
      }

      if(json.ok === undefined) json.ok = res.ok;
      if(!res.ok && !json.error) json.error = `HTTP ${res.status}`;

      return json;
    }catch(err){
      return { ok:false, error: err?.message || "Network error" };
    }
  };

  // ---------- Bootstrap Toasts (opcional) ----------
  window.toast = function(message, type="info"){
    // type: info|success|warning|danger
    // requiere bootstrap.bundle (Toast)
    if(!window.bootstrap?.Toast){
      // fallback: alert simple
      console.log(`[toast:${type}]`, message);
      return;
    }

    let host = qs("#toastHost");
    if(!host){
      host = document.createElement("div");
      host.id = "toastHost";
      host.className = "toast-container position-fixed bottom-0 end-0 p-3";
      document.body.appendChild(host);
    }

    const el = document.createElement("div");
    el.className = `toast text-bg-${type} border-0`;
    el.setAttribute("role","alert");
    el.setAttribute("aria-live","assertive");
    el.setAttribute("aria-atomic","true");
    el.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${esc(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    host.appendChild(el);

    const t = new bootstrap.Toast(el, { delay: 2500 });
    t.show();

    el.addEventListener("hidden.bs.toast", ()=> el.remove());
  };

  // ---------- Simple paginator (client-side) ----------
  window.paginate = function(items, page=1, perPage=10){
    page = Math.max(1, Number(page||1));
    perPage = Math.max(1, Number(perPage||10));
    const total = items.length;
    const pages = Math.max(1, Math.ceil(total / perPage));
    const start = (page - 1) * perPage;
    const data = items.slice(start, start + perPage);
    return { page, perPage, total, pages, data };
  };

  // ---------- Network debug helper ----------
  window.debugNoJson = function(resp){
    // resp viene de apiGet/apiPost cuando da No JSON
    // imprime en consola los primeros caracteres del raw
    console.warn("No JSON response", resp);
    if(resp?.raw){
      console.log("RAW (first 800 chars):", resp.raw.slice(0, 800));
    }
  };
})();
