(function () {
    const cards = window.SDC_CARDS || {};
    const roots = document.querySelectorAll('.sdc-root');
    if (!roots.length) {
        return;
    }

    roots.forEach((root) => {
        const id = root.getAttribute('id');
        const config = cards[id] || {};
        initCard(root, config);
    });

    function initCard(root, config) {
        const stage = root.querySelector('.sdc-stage');
        if (!stage) {
            return;
        }
        const designHeight = safeNum(config.designHeight, 860);

        if (config.lockPageScroll) {
            document.documentElement.classList.add('sdc-no-scroll');
            document.body.classList.add('sdc-no-scroll');
        }

        bindContact(root, config);
        bindSlides(root, config);
        bindModal(root, config);
        bindSaveContact(root, config);
        bindShare(root, config);

        function fit() {
            const viewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
            let top = root.getBoundingClientRect().top;

            let available;
            if (config.fitBetweenHeaderFooter) {
                const headerBottom = getHeaderBottom(root);
                if (top > headerBottom + 1) {
                    root.style.marginTop = (headerBottom - top) + 'px';
                    top = root.getBoundingClientRect().top;
                }

                const footerInfo = getFooterInfo(root);
                if (footerInfo && footerInfo.top > top) {
                    available = Math.floor(footerInfo.top - top);
                } else {
                    available = Math.floor(viewportHeight - top);
                }
            } else {
                root.style.marginTop = '0px';
                available = Math.floor(viewportHeight - top);
            }

            if (!Number.isFinite(available) || available < 320) {
                available = Math.floor(viewportHeight - top);
            }
            available = Math.max(320, available);

            const fitScaleRaw = available / designHeight;
            const fitScale = Math.min(1.12, Math.max(0.72, fitScaleRaw));
            root.style.setProperty('--sdc-root-h', available + 'px');
            root.style.setProperty('--sdc-fit-scale', String(fitScale));
            root.style.height = available + 'px';
            stage.style.transform = 'none';
        }

        fit();
        window.addEventListener('resize', fit);
        window.addEventListener('orientationchange', fit);
    }

    function bindContact(root, config) {
        const emailEl = root.querySelector('.sdc-email');
        const emailText = root.querySelector('.sdc-email-text');
        const waMain = root.querySelector('.sdc-wa-main');
        const waText = root.querySelector('.sdc-wa-text');
        const fb = root.querySelector('.sdc-fb');
        const ig = root.querySelector('.sdc-ig');
        const tt = root.querySelector('.sdc-tt');

        const email = String(config.email || '');
        const phone = String(config.phone || '');
        const phoneDisplay = String(config.phoneDisplay || phone);
        const digits = phone.replace(/[^\d]/g, '');

        if (emailEl) {
            emailEl.setAttribute('href', 'mailto:' + email);
        }
        if (emailText) {
            emailText.textContent = email.toUpperCase();
        }
        if (waMain) {
            waMain.setAttribute('href', (config.whatsappUrl && String(config.whatsappUrl)) ? String(config.whatsappUrl) : ('https://wa.me/' + digits));
        }
        if (waText) {
            waText.textContent = phoneDisplay;
        }
        if (fb) {
            fb.setAttribute('href', String(config.facebookUrl || '#'));
        }
        if (ig) {
            ig.setAttribute('href', String(config.instagramUrl || '#'));
        }
        if (tt) {
            tt.setAttribute('href', String(config.tiktokUrl || '#'));
        }
    }

    function bindSlides(root, config) {
        const slidesWrap = root.querySelector('.sdc-slides');
        const dotsWrap = root.querySelector('.sdc-dots');
        if (!slidesWrap || !dotsWrap) {
            return;
        }

        const srcs = Array.isArray(config.slides) ? config.slides.filter(Boolean) : [];
        const slides = [];
        const dots = [];
        let idx = 0;
        let timer = null;
        const interval = safeNum(config.autoplayInterval, 3800);

        srcs.forEach((src, i) => {
            const img = document.createElement('img');
            img.className = 'sdc-slide' + (i === 0 ? ' active' : '');
            img.src = src;
            img.alt = 'Showcase ' + (i + 1);
            slidesWrap.appendChild(img);
            slides.push(img);

            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'sdc-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => show(i, true));
            dotsWrap.appendChild(dot);
            dots.push(dot);
        });

        function show(next, reset) {
            if (!slides.length) {
                return;
            }
            idx = (next + slides.length) % slides.length;
            slides.forEach((s, i) => s.classList.toggle('active', i === idx));
            dots.forEach((d, i) => d.classList.toggle('active', i === idx));
            if (reset) {
                auto();
            }
        }

        function auto() {
            if (timer) {
                clearInterval(timer);
            }
            if (slides.length <= 1) {
                return;
            }
            timer = setInterval(() => show(idx + 1, false), interval);
        }

        auto();
    }

    function bindShare(root, config) {
        const shareBtn = root.querySelector('.sdc-share-btn');
        if (!shareBtn) {
            return;
        }

        shareBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            const phoneDisplay = String(config.phoneDisplay || config.phone || '');
            const whatsappUrl = String(config.whatsappUrl || '');
            const text = 'Switch Graphics contact: ' + phoneDisplay + ' ' + whatsappUrl;

            try {
                if (navigator.share) {
                    await navigator.share({
                        title: String(config.companyName || 'Switch Graphics'),
                        text: text,
                        url: whatsappUrl || undefined
                    });
                    return;
                }
            } catch (error) {
                // Intentionally silent.
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                    alert('Contact info copied for sharing.');
                    return;
                }
            } catch (error) {
                // Intentionally silent.
            }

            window.prompt('Copy this:', text);
        });
    }

    function bindSaveContact(root, config) {
        const saveBtn = root.querySelector('.sdc-save');
        if (!saveBtn) {
            return;
        }

        saveBtn.addEventListener('click', async (event) => {
            event.preventDefault();

            const vcf = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                'FN:' + String(config.companyName || ''),
                'ORG:' + String(config.companyName || ''),
                'TEL;TYPE=CELL:' + String(config.phone || ''),
                'EMAIL;TYPE=INTERNET:' + String(config.email || ''),
                'URL:' + String(config.websiteUrl || ''),
                'END:VCARD'
            ].join('\n');

            try {
                const file = new File([vcf], 'switch-digital-card-contact.vcf', { type: 'text/vcard' });
                if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Add Contact',
                        text: String(config.companyName || '')
                    });
                    return;
                }
            } catch (error) {
                // Intentionally silent.
            }

            window.location.href = 'data:text/vcard;charset=utf-8,' + encodeURIComponent(vcf);
        });
    }

    function bindModal(root, config) {
        const otherBtn = root.querySelector('.sdc-other');
        const modal = root.nextElementSibling && root.nextElementSibling.classList.contains('sdc-modal') ? root.nextElementSibling : null;
        if (!otherBtn || !modal) {
            return;
        }

        const closeBtn = modal.querySelector('.sdc-modal-close');
        const linksList = modal.querySelector('.sdc-links-list');

        function renderLinks() {
            if (!linksList) {
                return;
            }

            const links = mergeLinks(config);
            linksList.innerHTML = links.map((item) => {
                return '<a class="sdc-link" href="' + escapeHtml(item.href) + '"><span>' + escapeHtml(item.label) + '</span><span>›</span></a>';
            }).join('');
        }

        function openModal() {
            renderLinks();
            modal.hidden = false;
            requestAnimationFrame(() => {
                modal.classList.add('is-open');
            });
        }

        function closeModal() {
            modal.classList.remove('is-open');
            setTimeout(() => {
                modal.hidden = true;
            }, 200);
        }

        otherBtn.addEventListener('click', openModal);
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    }

    function mergeLinks(config) {
        const defaults = [
            { label: 'Website', href: String(config.websiteUrl || '') },
            { label: 'WhatsApp', href: String(config.whatsappUrl || '') },
            { label: 'Facebook', href: String(config.facebookUrl || '') },
            { label: 'Instagram', href: String(config.instagramUrl || '') },
            { label: 'TikTok', href: String(config.tiktokUrl || '') }
        ];

        const custom = Array.isArray(config.customLinks) ? config.customLinks : [];
        const menu = Array.isArray(config.menuLinks) ? config.menuLinks : [];
        const merged = [];
        const seen = new Set();

        [...menu, ...custom, ...defaults].forEach((item) => {
            if (!item || !item.href) {
                return;
            }
            const href = String(item.href);
            if (!href || seen.has(href)) {
                return;
            }
            seen.add(href);
            merged.push({
                label: String(item.label || href),
                href: href
            });
        });

        return merged;
    }

    function getHeaderBottom(root) {
        const selectors = '.elementor-location-header, .site-header, header.site-header, #masthead, header';
        let bestBottom = 0;

        document.querySelectorAll(selectors).forEach((el) => {
            if (root.contains(el)) {
                return;
            }
            const rect = el.getBoundingClientRect();
            if (rect.height <= 0) {
                return;
            }
            if (rect.top > window.innerHeight * 0.5) {
                return;
            }
            if (rect.bottom > bestBottom) {
                bestBottom = rect.bottom;
            }
        });

        return bestBottom > 0 ? bestBottom : 0;
    }

    function getFooterInfo(root) {
        const selectors = '.elementor-location-footer, footer, #colophon, .site-footer';
        let best = null;
        document.querySelectorAll(selectors).forEach((el) => {
            if (root.contains(el)) {
                return;
            }
            const rect = el.getBoundingClientRect();
            if (rect.height <= 0) {
                return;
            }

            const candidate = {
                top: rect.top,
                height: rect.height
            };

            if (!best || candidate.top < best.top) {
                best = candidate;
            }
        });

        return best;
    }

    function safeNum(value, fallback) {
        const num = Number(value);
        return Number.isFinite(num) ? num : fallback;
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, (char) => {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return map[char] || char;
        });
    }
})();
