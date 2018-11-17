(function() {

	var autotyper = undefined;

	var Autotyper = function(autotypeElement) {
	    this.element = autotypeElement;
	    this.autotypeList = JSON.parse(autotypeElement.getAttribute('data-to-type'));
	    this.wordIndex = 0;
	    this.currentText = '';
	    this.isDeleting = false;

	    this.tick();
	}

	Autotyper.prototype.tick = function() {
		var word = this.autotypeList[this.wordIndex];

		var action = 1;
		if (this.isDeleting) {
			action = -1;
		}
		this.currentText = word.substring(0, this.currentText.length + action);
		this.element.innerHTML = this.currentText;

		var delta = this.isDeleting ? 75 : 125;

		if (this.currentText === word) {
			this.isDeleting = true;
			delta = 1000;
			if (this.wordIndex === this.autotypeList.length - 1) {
				delta = 4000;
			}
		}

		if (this.isDeleting && this.currentText === '') {
			this.wordIndex++;
			if (this.wordIndex === this.autotypeList.length) {
				this.wordIndex = 0;
			}
			this.isDeleting = false;
		}

		var self = this;
		setTimeout(function() {
			self.tick();
		}, delta);
	};

	var autotypeElement = document.getElementById('autotype');
	if (autotypeElement) {
		autotyper = new Autotyper(autotypeElement);
	}

})()