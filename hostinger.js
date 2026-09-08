/* Hostinger runtime adapter.
   Replaces the legacy Netlify endpoint paths with local Hostinger PHP endpoints. */
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
   app.js predates the finalized 3-slot theory rule, so remove only its legacy
   validation message without changing any other conflict checks. */
(function patchTheoryRule() {
  function patch() {
    if (typeof window.issues !== 'function' || window.__uvasTheoryRulePatched) return;
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
    window.__uvasTheoryRulePatched = true;
  }
  patch();
  setTimeout(patch, 0);
  window.addEventListener('load', patch);
})();
