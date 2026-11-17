/*
 * Satisfy UI behaviour. Plain JavaScript, no dependencies.
 *
 * - Dismiss flash messages
 * - Add and remove entries of Symfony collection fields (see templates/form/theme.html.twig)
 * - Copy the composer.json snippet and package require lines
 * - Filter the package list (templates/package_page.html.twig)
 * - Stream the Satis build log
 */
(() => {
    'use strict';

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
            return;
        }

        const dismiss = target.closest('[data-dismiss="alert"]');
        if (dismiss) {
            const alert = dismiss.closest('.alert');
            alert.classList.add('hiding');
            window.setTimeout(() => alert.remove(), 200);
            return;
        }

        const add = target.closest('[data-collection-add]');
        if (add) {
            addCollectionEntry(add.closest('[data-collection]'));
            return;
        }

        const remove = target.closest('[data-collection-remove]');
        if (remove) {
            const collection = remove.closest('[data-collection]');
            remove.closest('[data-collection-entry]').remove();
            collection.querySelector('[data-collection-add]')?.focus();
            return;
        }

        const copyText = target.closest('[data-copy-text]');
        if (copyText) {
            copyRequireLine(copyText);
        }
    });

    async function copyRequireLine(button) {
        const text = button.dataset.copyText;
        // The Clipboard API needs a secure context (HTTPS or localhost); show the line to copy by hand otherwise.
        if (!navigator.clipboard) {
            window.prompt('Copy this line to the "require" section of composer.json:', text);
            return;
        }
        const status = document.querySelector('[data-copy-status]');
        try {
            await navigator.clipboard.writeText(text);
            status.textContent = `Copied ${text}`;
            button.classList.add('is-copied');
            window.setTimeout(() => button.classList.remove('is-copied'), 1500);
        } catch {
            window.prompt('Copy this line to the "require" section of composer.json:', text);
        }
    }

    const packageFilter = document.querySelector('[data-package-filter]');
    if (packageFilter) {
        const packages = [...document.querySelectorAll('[data-package]')];
        const count = document.querySelector('[data-package-count]');
        const empty = document.querySelector('[data-package-empty]');
        const plural = (n) => (n === 1 ? 'package' : 'packages');

        packageFilter.addEventListener('input', () => {
            const terms = packageFilter.value.toLowerCase().split(/\s+/).filter(Boolean);
            let visible = 0;
            packages.forEach((item) => {
                item.hidden = !terms.every((term) => item.dataset.package.includes(term));
                visible += item.hidden ? 0 : 1;
            });
            count.textContent = terms.length
                ? `${visible} of ${packages.length} ${plural(packages.length)}`
                : `${packages.length} ${plural(packages.length)}`;
            empty.hidden = visible > 0;
        });
    }

    function addCollectionEntry(collection) {
        const index = Number(collection.dataset.nextIndex || 0);
        const placeholder = collection.dataset.prototypeName || '__name__';
        const template = document.createElement('template');
        template.innerHTML = collection.dataset.prototype.replaceAll(placeholder, String(index)).trim();

        const entry = template.content.firstElementChild;
        collection.querySelector('[data-collection-entries]').append(entry);
        collection.dataset.nextIndex = String(index + 1);
        entry.querySelector('input, select, textarea')?.focus();
    }

    document.querySelectorAll('[data-copy]').forEach((button) => {
        // The Clipboard API needs a secure context (HTTPS or localhost); without it the button stays hidden.
        if (!navigator.clipboard) {
            return;
        }
        const label = button.querySelector('[data-copy-label]');
        let reset;
        button.hidden = false;
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(document.querySelector(button.dataset.copy).textContent);
                label.textContent = 'Copied';
            } catch {
                label.textContent = 'Copy failed';
            }
            window.clearTimeout(reset);
            reset = window.setTimeout(() => { label.textContent = 'Copy'; }, 2000);
        });
    });

    const buildLog = document.querySelector('[data-build-log]');
    if (buildLog) {
        streamBuild(buildLog);
    }

    function streamBuild(buildLog) {
        const output = buildLog.querySelector('[data-build-output]');
        const status = buildLog.querySelector('[data-build-status]');
        const source = new EventSource(buildLog.dataset.source);
        let lastLine = '';

        const setState = (state, text) => {
            buildLog.dataset.state = state;
            status.textContent = text;
        };

        source.addEventListener('open', () => setState('running', 'Building…'));

        source.addEventListener('message', (event) => {
            // The server sends the command line first, then the output, then the exit code text, then "__done__".
            if (event.data === '__done__') {
                source.close();
                if (lastLine === 'OK') {
                    setState('success', 'Build finished');
                } else {
                    setState('failed', `Build failed: ${lastLine}`);
                }
                return;
            }

            const followOutput = window.innerHeight + window.scrollY >= document.body.scrollHeight - 48;
            const line = document.createElement('span');
            line.className = output.childElementCount === 0 ? 'console-command' : 'console-line';
            line.textContent = `${event.data}\n`;
            output.append(line);
            lastLine = event.data;
            if (followOutput) {
                window.scrollTo(0, document.body.scrollHeight);
            }
        });

        // EventSource reconnects on its own after an error, which would start a second build.
        source.addEventListener('error', () => {
            if (buildLog.dataset.state === 'running') {
                source.close();
                setState('failed', 'Connection lost. Reload the page to start the build again.');
            }
        });
    }
})();
