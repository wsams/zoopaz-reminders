;(function() {
    window.parent.postMessage(["childHeight",
        document.getElementsByTagName("html")[0].scrollHeight], "*");
})();
