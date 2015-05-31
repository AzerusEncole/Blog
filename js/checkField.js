function check (elem, maxSymbols, displayElem) {
    elem.oninput = function () {
        var current = elem.value.length;
        var diff = maxSymbols - current;

        if (displayElem) displayElem.innerHTML = diff;

        if (diff <= 0) {
            if (displayElem) displayElem.innerHTML = 0;
            elem.value = elem.value.substring(0, maxSymbols);
        }
    }
}