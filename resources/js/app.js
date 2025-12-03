import './bootstrap';
// Alpine removed here; Livewire provides its own instance where needed to avoid duplicate warning.

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
	// Update footer year safely
	const y = document.getElementById('year-copy');
	if (y) { const now = new Date().getFullYear(); if (Number(y.textContent) !== now) y.textContent = String(now); }

	// Mark external links with rel noopener noreferrer for security best practices
	for (const a of document.querySelectorAll('a[href^="http"], a[target="_blank"]')) {
		try {
			const url = new URL(a.href);
			if (url.host !== window.location.host) {
				a.target = a.target || '_blank';
				const rel = new Set((a.getAttribute('rel') || '').split(/\s+/).filter(Boolean));
				rel.add('noopener'); rel.add('noreferrer');
				a.setAttribute('rel', Array.from(rel).join(' '));
			}
		} catch(_) { /* ignore malformed */ }
	}

	const btn = document.querySelector('[data-menu-button]');
	const panel = document.querySelector('[data-menu-panel]');
	if (btn && panel) {
		const toggle = () => {
			const open = panel.classList.toggle('hidden') === false; // hidden removed => open
			btn.setAttribute('aria-expanded', String(open));
			btn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
			btn.innerHTML = open
				? '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
				: '<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>';
		};
		btn.addEventListener('click', toggle);
	}

	// Header shrink on scroll + back-to-top visibility
	const header = document.getElementById('site-header');
	const backToTop = document.querySelector('[data-backtotop]');
	const onScroll = () => {
		const scrolled = window.scrollY > 10;
		if (header) header.style.boxShadow = scrolled ? '0 6px 20px rgba(0,0,0,.25)' : 'none';
		if (backToTop) backToTop.classList.toggle('hidden', !scrolled);
	};
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	// Back to top
	if (backToTop) backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

	// Simple newsletter validation (UI only)
	const form = document.querySelector('[data-newsletter]');
	if (form) {
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			const input = form.querySelector('input[type="email"]');
			const ok = input && /.+@.+\..+/.test(input.value.trim());
			form.querySelector('[data-newsletter-success]')?.classList.toggle('hidden', !ok);
			form.querySelector('[data-newsletter-error]')?.classList.toggle('hidden', ok);
		});
	}

	const initGalleryLightbox = () => {
		const grid = document.querySelector('[data-gallery-grid]');
		const lightbox = document.querySelector('[data-lightbox]');
		if (!grid || !lightbox) return;
		const imgEl = lightbox.querySelector('[data-lightbox-image]');
		const caption = lightbox.querySelector('[data-lightbox-caption]');
		const closeBtn = lightbox.querySelector('[data-lightbox-close]');
		const prevBtn = lightbox.querySelector('[data-lightbox-prev]');
		const nextBtn = lightbox.querySelector('[data-lightbox-next]');
		const triggers = [...grid.querySelectorAll('[data-gallery-trigger]')];
		let current = -1;
		let lastFocused = null;
		const focusableSelector = 'button, [href], [tabindex]:not([tabindex="-1"])';

		const setImage = () => {
			const trigger = triggers[current];
			if (!trigger) return;
			const image = trigger.querySelector('img');
			if (!image || !imgEl) return;
			imgEl.src = image.currentSrc || image.src;
			imgEl.alt = image.alt;
			if (caption) caption.textContent = image.alt;
		};

		const open = (idx) => {
			current = idx;
			lastFocused = document.activeElement;
			setImage();
			lightbox.classList.remove('hidden');
			lightbox.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			(closeBtn || lightbox).focus();
		};

		const close = () => {
			lightbox.classList.add('hidden');
			lightbox.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
			lastFocused?.focus();
		};

		const prev = () => {
			if (!triggers.length) return;
			current = (current - 1 + triggers.length) % triggers.length;
			setImage();
		};

		const next = () => {
			if (!triggers.length) return;
			current = (current + 1) % triggers.length;
			setImage();
		};

		const trapFocus = (event) => {
			if (lightbox.classList.contains('hidden') || event.key !== 'Tab') return;
			const focusables = [...lightbox.querySelectorAll(focusableSelector)].filter((el) => !el.hasAttribute('disabled'));
			if (!focusables.length) return;
			const first = focusables[0];
			const last = focusables[focusables.length - 1];
			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		};

		const handleKeydown = (event) => {
			if (lightbox.classList.contains('hidden')) return;
			switch (event.key) {
				case 'Escape':
					event.preventDefault();
					close();
					break;
				case 'ArrowLeft':
					event.preventDefault();
					prev();
					break;
				case 'ArrowRight':
					event.preventDefault();
					next();
					break;
				default:
					break;
			}
		};

		triggers.forEach((btn, index) => {
			btn.addEventListener('click', () => open(index));
			btn.addEventListener('keydown', (event) => {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					open(index);
				}
			});
		});

		closeBtn?.addEventListener('click', close);
		prevBtn?.addEventListener('click', prev);
		nextBtn?.addEventListener('click', next);
		lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
		lightbox.addEventListener('keydown', trapFocus);
		document.addEventListener('keydown', handleKeydown);
	};

	const initCalendarViewers = () => {
		document.querySelectorAll('[data-calendar-viewer]').forEach((viewer) => {
			let images = [];
			try {
				images = JSON.parse(viewer.dataset.calendarImages || '[]');
			} catch (_) {
				images = [];
			}
			if (!Array.isArray(images) || !images.length) return;
			const modal = viewer.querySelector('[data-calendar-modal]');
			const overlay = modal?.querySelector('[data-calendar-dismiss]');
			const surface = modal?.querySelector('[data-calendar-dismiss-surface]');
			const imageEl = modal?.querySelector('[data-calendar-image]');
			const zoomLabel = modal?.querySelector('[data-calendar-zoom]');
			const download = modal?.querySelector('[data-calendar-download]');
			const prevBtn = modal?.querySelector('[data-calendar-prev]');
			const nextBtn = modal?.querySelector('[data-calendar-next]');
			const closeBtn = modal?.querySelector('[data-calendar-close]');
			const zoomInBtn = modal?.querySelector('[data-calendar-zoom-in]');
			const zoomOutBtn = modal?.querySelector('[data-calendar-zoom-out]');
			const focusableSelector = 'button, [href], [tabindex]:not([tabindex="-1"])';
			let current = 0;
			let zoom = 1;
			let lastFocused = null;

			const updateZoom = () => {
				if (imageEl) {
					imageEl.style.transform = `scale(${zoom})`;
					imageEl.style.transformOrigin = 'center';
				}
				if (zoomLabel) zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
			};

			const setImage = () => {
				if (!imageEl) return;
				imageEl.src = images[current];
				imageEl.alt = `Calendario del Mes del Rosario — página ${current + 1}`;
				if (download) {
					download.href = images[current];
					download.setAttribute('download', `calendario-${current + 1}.jpg`);
				}
				updateZoom();
			};

			const open = (idx = 0) => {
				if (!modal) return;
				current = idx;
				zoom = 1;
				lastFocused = document.activeElement;
				setImage();
				modal.classList.remove('hidden');
				modal.setAttribute('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
				const focusables = [...modal.querySelectorAll(focusableSelector)].filter((el) => !el.hasAttribute('disabled'));
				(focusables[0] || closeBtn || modal).focus();
			};

			const close = () => {
				if (!modal) return;
				modal.classList.add('hidden');
				modal.setAttribute('aria-hidden', 'true');
				document.body.style.overflow = '';
				lastFocused?.focus();
			};

			const next = () => {
				current = (current + 1) % images.length;
				setImage();
			};

			const prev = () => {
				current = (current - 1 + images.length) % images.length;
				setImage();
			};

			const zoomIn = () => {
				zoom = Math.min(3, zoom + 0.1);
				updateZoom();
			};

			const zoomOut = () => {
				zoom = Math.max(0.5, zoom - 0.1);
				updateZoom();
			};

			const trapFocus = (event) => {
				if (!modal || modal.classList.contains('hidden') || event.key !== 'Tab') return;
				const focusables = [...modal.querySelectorAll(focusableSelector)].filter((el) => !el.hasAttribute('disabled'));
				if (!focusables.length) return;
				const first = focusables[0];
				const last = focusables[focusables.length - 1];
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			};

			const handleKeydown = (event) => {
				if (!modal || modal.classList.contains('hidden')) return;
				switch (event.key) {
					case 'Escape':
						event.preventDefault();
						close();
						break;
					case 'ArrowLeft':
						event.preventDefault();
						prev();
						break;
					case 'ArrowRight':
						event.preventDefault();
						next();
						break;
					case '+':
					case '=':
						event.preventDefault();
						zoomIn();
						break;
					case '-':
						event.preventDefault();
						zoomOut();
						break;
					default:
						break;
				}
			};

			viewer.querySelectorAll('[data-calendar-open]').forEach((btn) => {
				btn.addEventListener('click', () => {
					const idx = Number(btn.dataset.calendarOpen) || 0;
					open(idx);
				});
				btn.addEventListener('keydown', (event) => {
					if (event.key === 'Enter' || event.key === ' ') {
						event.preventDefault();
						const idx = Number(btn.dataset.calendarOpen) || 0;
						open(idx);
					}
				});
			});

			overlay?.addEventListener('click', close);
			closeBtn?.addEventListener('click', close);
			prevBtn?.addEventListener('click', prev);
			nextBtn?.addEventListener('click', next);
			zoomInBtn?.addEventListener('click', zoomIn);
			zoomOutBtn?.addEventListener('click', zoomOut);
			surface?.addEventListener('click', (event) => {
				if (event.target === surface) close();
			});
			modal?.addEventListener('keydown', trapFocus);
			document.addEventListener('keydown', handleKeydown);
		});
	};

	const initIntentionForm = () => {
		const form = document.querySelector('[data-intention-form]');
		if (!form) return;
		const massSelect = form.querySelector('#mass_instance_id');
		const typeRadios = form.querySelectorAll('input[name="intention_type"]');
		const summary = form.querySelector('[data-intention-summary]');
		const summaryType = summary?.querySelector('[data-intention-type]');
		const summaryPrice = summary?.querySelector('[data-intention-price]');
		const summaryMass = summary?.querySelector('[data-intention-mass]');

		const updateSummary = () => {
			const checked = form.querySelector('input[name="intention_type"]:checked');
			if (summaryType) summaryType.textContent = checked?.dataset.label || 'Selecciona un tipo';
			if (summaryPrice) summaryPrice.textContent = checked?.dataset.price || '—';
			if (summaryMass) summaryMass.textContent = massSelect?.selectedOptions?.[0]?.text?.trim() || 'Elige una fecha';
		};

		const normalizeType = (value) => (value === 'rosario' ? 'rosario' : 'regular');

		const filterMasses = () => {
			if (!massSelect) return;
			const selectedType = normalizeType(form.querySelector('input[name="intention_type"]:checked')?.value || 'normal');
			[...massSelect.options].forEach((option) => {
				if (!option.value) {
					option.hidden = false;
					option.disabled = false;
					return;
				}
				const massType = option.dataset.massType || 'regular';
				const allow = selectedType === 'rosario' ? massType === 'rosario' : massType !== 'rosario';
				option.hidden = !allow;
				option.disabled = !allow;
			});
			const current = massSelect.options[massSelect.selectedIndex];
			if (current?.disabled) {
				massSelect.value = '';
			}
			updateSummary();
		};

		typeRadios.forEach((radio) => radio.addEventListener('change', () => {
			filterMasses();
			updateSummary();
		}));
		massSelect?.addEventListener('change', updateSummary);

		filterMasses();
		updateSummary();
	};

	initGalleryLightbox();
	initCalendarViewers();
	initIntentionForm();
});
