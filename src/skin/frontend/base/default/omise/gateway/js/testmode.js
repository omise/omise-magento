
window.addEventListener('load', function() {
	var warning = document.createElement('div');
	warning.innerHTML = 'OMISE TEST MODE - <a target="_blank" href="https://www.omise.co/what-are-test-keys-and-live-keys">[more info here]</a>';
	warning.className = 'omise-test-warning';
	document.body.appendChild(warning);
});