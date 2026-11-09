(() => {
  const root = document.documentElement;
  root.dataset.theme = localStorage.getItem('vermont-theme') || 'light';

  const openAdminAnchor = () => {
    if (!window.location.hash) return;
    const target = document.getElementById(decodeURIComponent(window.location.hash.slice(1)));
    if (!target) return;
    if (target instanceof HTMLDetailsElement) target.open = true;
    window.requestAnimationFrame(() => target.scrollIntoView({behavior: 'smooth', block: 'start'}));
  };
  window.addEventListener('load', openAdminAnchor);
  window.addEventListener('hashchange', openAdminAnchor);

  document.querySelector('#theme-toggle')?.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('vermont-theme', root.dataset.theme);
  });

  document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    await navigator.clipboard.writeText(button.dataset.copy);
    button.textContent = button.dataset.copied || 'Nusxalandi ✓';
  }));

  const flipper = document.querySelector('#auth-flipper');
  document.querySelectorAll('[data-auth-flip]').forEach(button => {
    button.addEventListener('click', () => {
      const isFlipped = flipper?.classList.toggle('is-flipped') ?? false;
      document.querySelectorAll('[data-feedback]').forEach(box => { box.textContent = ''; });
      const nextInput = document.querySelector(isFlipped ? '.auth-back input' : '.auth-front input');
      window.setTimeout(() => nextInput?.focus(), 850);
    });
  });

  document.querySelectorAll('[data-ajax-auth]').forEach(form => {
    form.addEventListener('submit', async event => {
      event.preventDefault();
      const feedback = form.closest('.auth-face')?.querySelector('[data-feedback]');
      const submit = form.querySelector('button[type="submit"]');
      const originalText = submit?.textContent;
      if (feedback) feedback.textContent = '';
      if (submit) { submit.disabled = true; submit.textContent = '...'; }

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const result = await response.json();

        if (!response.ok) {
          const messages = result.errors
            ? Object.values(result.errors).flat()
            : [result.message || form.dataset.requestErrorText || 'So‘rov bajarilmadi. Qayta urinib ko‘ring.'];
          if (feedback) feedback.textContent = messages.join(' ');
          Object.entries(result.errors || {}).forEach(([field, fieldMessages]) => {
            if (!['name', 'email'].includes(field)) return;
            const hint = form.querySelector(`[data-hint-for="${field}"]`);
            if (hint) {
              hint.textContent = fieldMessages[0];
              hint.className = 'availability-hint is-taken';
            }
          });
          return;
        }

        if (result.redirect) {
          window.location.assign(result.redirect);
          return;
        }
        if (feedback) feedback.textContent = result.message || 'Amal bajarildi.';
      } catch (error) {
        if (feedback) feedback.textContent = form.dataset.networkErrorText || 'Server bilan bog‘lanib bo‘lmadi. Internet yoki serverni tekshiring.';
      } finally {
        if (submit) { submit.disabled = false; submit.textContent = originalText; }
      }
    });
  });

  document.querySelectorAll('[data-availability]').forEach(input => {
    let timer;
    let activeRequest;
    const availabilityForm = input.closest('form');
    const hint = availabilityForm?.querySelector(`[data-hint-for="${input.dataset.availability}"]`);

    const setHint = (message, state = '') => {
      if (!hint) return;
      hint.textContent = message;
      hint.className = `availability-hint ${state}`.trim();
    };

    const check = async () => {
      const value = input.value.trim();
      const field = input.dataset.availability;
      if (!value) { setHint(''); return; }
      if (field === 'name' && value.length < 2) { setHint(''); return; }
      if (field === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        setHint(availabilityForm?.dataset.invalidEmailText || 'Email manzilini to‘liq kiriting.', 'is-neutral');
        return;
      }

      activeRequest?.abort();
      activeRequest = new AbortController();
      setHint(availabilityForm?.dataset.checkingText || 'Tekshirilmoqda…', 'is-checking');
      const query = new URLSearchParams({field, value});

      try {
        const response = await fetch(`${availabilityForm?.dataset.availabilityUrl || ''}?${query}`, {
          credentials: 'same-origin',
          signal: activeRequest.signal,
          headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Tekshirib bo‘lmadi.');
        setHint(result.message, result.valid ? (result.available ? 'is-available' : 'is-taken') : 'is-neutral');
      } catch (error) {
        if (error.name !== 'AbortError') setHint(availabilityForm?.dataset.networkErrorText || 'Tekshirish vaqtincha imkonsiz.', 'is-neutral');
      }
    };

    input.addEventListener('input', () => {
      clearTimeout(timer);
      activeRequest?.abort();
      timer = setTimeout(check, 450);
    });
    input.addEventListener('blur', check);
  });

  // Replace native select controls with accessible, styled glass menus.
  const closeCategoryMenus = except => {
    document.querySelectorAll('.glass-select.is-open').forEach(wrapper => {
      if (wrapper === except) return;
      wrapper.classList.remove('is-open');
      wrapper.querySelector('.glass-select-trigger')?.setAttribute('aria-expanded', 'false');
      const menu = wrapper.querySelector('.glass-select-menu');
      if (menu) menu.hidden = true;
    });
  };

  document.querySelectorAll('select').forEach(select => {
    if (select.dataset.glassEnhanced) return;
    select.dataset.glassEnhanced = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = `glass-select${select.classList.contains('locale-select') ? ' glass-select-locale' : ''}`;
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'glass-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-label', select.getAttribute('aria-label') || `${select.name || 'Tanlov'} tanlash`);

    const label = document.createElement('span');
    label.className = 'glass-select-value';
    const arrow = document.createElement('span');
    arrow.className = 'glass-select-arrow';
    arrow.setAttribute('aria-hidden', 'true');
    trigger.append(label, arrow);

    const menu = document.createElement('div');
    menu.className = 'glass-select-menu';
    menu.setAttribute('role', 'listbox');
    menu.setAttribute('aria-label', select.name === 'category' ? 'Kategoriyalar' : 'Tanlovlar');
    menu.hidden = true;

    const renderSelection = () => {
      label.textContent = select.selectedOptions[0]?.textContent.trim() || 'Kategoriya tanlang';
      menu.querySelectorAll('[role="option"]').forEach(optionButton => {
        const selected = optionButton.dataset.value === select.value;
        optionButton.setAttribute('aria-selected', String(selected));
        optionButton.classList.toggle('is-selected', selected);
      });
    };

    Array.from(select.options).forEach(option => {
      const optionButton = document.createElement('button');
      optionButton.type = 'button';
      optionButton.className = 'glass-select-option';
      optionButton.setAttribute('role', 'option');
      optionButton.dataset.value = option.value;
      optionButton.textContent = option.textContent.trim();
      optionButton.disabled = option.disabled;
      optionButton.addEventListener('click', () => {
        select.value = option.value;
        select.dispatchEvent(new Event('input', {bubbles: true}));
        select.dispatchEvent(new Event('change', {bubbles: true}));
        renderSelection();
        wrapper.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        menu.hidden = true;
        trigger.focus();
      });
      optionButton.addEventListener('keydown', event => {
        const options = Array.from(menu.querySelectorAll('.glass-select-option:not(:disabled)'));
        const currentIndex = options.indexOf(optionButton);
        let nextIndex = null;
        if (event.key === 'ArrowDown') nextIndex = (currentIndex + 1) % options.length;
        if (event.key === 'ArrowUp') nextIndex = (currentIndex - 1 + options.length) % options.length;
        if (event.key === 'Home') nextIndex = 0;
        if (event.key === 'End') nextIndex = options.length - 1;
        if (nextIndex !== null) {
          event.preventDefault();
          options[nextIndex]?.focus();
        }
        if (event.key === 'Escape') {
          wrapper.classList.remove('is-open');
          trigger.setAttribute('aria-expanded', 'false');
          menu.hidden = true;
          trigger.focus();
        }
      });
      menu.appendChild(optionButton);
    });

    const openMenu = () => {
      closeCategoryMenus(wrapper);
      wrapper.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      menu.hidden = false;
      (menu.querySelector('.is-selected') || menu.querySelector('.glass-select-option:not(:disabled)'))?.focus();
    };

    trigger.addEventListener('click', () => {
      if (wrapper.classList.contains('is-open')) {
        wrapper.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        menu.hidden = true;
      } else {
        openMenu();
      }
    });
    trigger.addEventListener('keydown', event => {
      if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        openMenu();
      }
    });

    select.parentNode.insertBefore(wrapper, select);
    wrapper.append(select, trigger, menu);
    select.classList.add('glass-select-native');
    select.addEventListener('change', renderSelection);
    select.addEventListener('invalid', () => trigger.focus());
    select.form?.addEventListener('reset', () => window.setTimeout(renderSelection, 0));
    renderSelection();
  });

  document.addEventListener('click', event => {
    if (!event.target.closest('.glass-select')) closeCategoryMenus();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeCategoryMenus();
  });
})();
