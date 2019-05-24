
window.addEventListener('load', function() {
	var warning = document.createElement('div'); 
	warning.innerHTML = Translator.translate('OMISE TEST MODE') + ' - \
		<a target="_blank" href="https://www.omise.co/what-are-test-keys-and-live-keys">\
		[ ' + Translator.translate('more info') +' ]</a>';
	warning.className = 'omise-test-warning';
	document.body.appendChild(warning);
});