async function apiGet(url){
  const r = await fetch(url, { credentials:"same-origin" });
  const t = await r.text();
  try { return JSON.parse(t); } catch { return { ok:false, error:"No JSON", raw:t }; }
}
async function apiPost(url, fd){
  const r = await fetch(url, { method:"POST", body:fd, credentials:"same-origin" });
  const t = await r.text();
  try { return JSON.parse(t); } catch { return { ok:false, error:"No JSON", raw:t }; }
}
function esc(s){
  return (s??"").toString().replace(/[&<>"']/g, m => ({
    "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
  }[m]));
}
function money(c){ return "$" + (Number(c||0)/100).toFixed(2); }
