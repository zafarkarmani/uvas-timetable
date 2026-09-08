/* Hostinger runtime adapter.
   Keeps the existing application stable while replacing Netlify endpoints with local PHP endpoints. */
(function () {
  const nativeFetch = window.fetch.bind(window);
  window.fetch = function (input, init) {
    if (typeof input === 'string') {
      if (input === '/.netlify/functions/config') input = '/api/config.php';
      if (input === '/.netlify/functions/gemini') input = '/api/gemini.php';
    }
    return nativeFetch(input, init);
  };
})();

/* The production scheduler permits 1, 2, or 3 consecutive theory slots.
   This compatibility patch removes the old 1/2-only validation message from app.js. */
window.addEventListener('load', function () {
  if (typeof window.issues !== 'function') return;
  const legacyIssues = window.issues;
  window.issues = function (entry, list) {
    const result = legacyIssues(entry, list);
    if (entry && entry.session_type === 'Theory' && Number(entry.duration_slots) === 3) {
      return result.filter(function (message) {
        return message !== 'Theory must use 1 or 2 consecutive slots';
      });
    }
    return result;
  };
});
