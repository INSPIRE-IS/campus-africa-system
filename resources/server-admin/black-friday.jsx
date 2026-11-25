import { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './black-friday.css';

const root = document.getElementById('black-friday');
const date = new Intl.DateTimeFormat('en-ZA', {
    dateStyle: 'medium', timeStyle: 'short', timeZone: 'Africa/Johannesburg',
});
const labels = { match: 'Matches', mismatch: 'Mismatch', missing: 'Missing', observed: 'Not compared' };
const time = (value) => date.format(new Date(value));
const fee = (value) => value === null || value === '' ? 'Not set' : `R ${value}`;

function Checker() {
    const [result, setResult] = useState(null);
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(true);
    const request = useRef(null);

    async function check() {
        request.current?.abort();
        const controller = new AbortController();
        request.current = controller;
        setBusy(true);
        setError('');
        try {
            const response = await fetch(root.dataset.statusUrl, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (response.status === 401 || response.status === 403) {
                throw new Error('Access denied or your session has expired. Sign in and try again.');
            }
            if (response.status === 429) {
                throw new Error('Too many checks. Wait one minute and try again.');
            }
            if (!response.ok || response.redirected) {
                throw new Error('Could not check live WordPress fees. Try again or check the application logs.');
            }
            setResult(await response.json());
        } catch (failure) {
            if (!controller.signal.aborted) {
                setError(failure.message);
            }
        } finally {
            if (!controller.signal.aborted) {
                setBusy(false);
            }
        }
    }

    useEffect(() => {
        check();
        return () => request.current?.abort();
    }, []);

    const problems = result?.rows.filter(({ result }) => result === 'mismatch' || result === 'missing').length ?? 0;
    const matched = result?.rows.filter(({ result }) => result === 'match').length ?? 0;
    const allMatched = matched > 0 && matched === result?.rows.length;
    const verified = result?.snapshot && allMatched && (
        (result.phase === 'promotion' && result.state === 'active') ||
        (result.phase === 'rollback' && result.state === 'restored')
    );
    const summary = problems ? `${problems} ${problems === 1 ? 'item needs' : 'items need'} attention`
        : verified ? (result.phase === 'promotion' ? 'Activation verified' : 'Restoration verified')
        : result?.phase === 'upcoming' ? 'Before launch'
        : !result?.snapshot ? 'No saved snapshot'
        : allMatched ? 'Fees match; scheduler state unconfirmed' : 'Not verified';

    return <>
        <header className="topbar">
            <div>
                <img src={root.dataset.logoUrl} alt="Campus Africa" width="38" height="38" />
                <strong>Server Admin</strong>
            </div>
            <nav aria-label="Admin"><a href="/metrics-features">Metrics</a><a href="/metrics-pricing">Pricing Inspector</a></nav>
        </header>
        <main>
            <div className="heading">
                <div><p className="eyebrow">WORDPRESS / PROMOTIONS</p><h1>Black Friday booking fees</h1></div>
                <button type="button" onClick={check} disabled={busy}>Check live fees</button>
            </div>
            {busy && <p role="status">Checking live WordPress fees...</p>}
            {error && <p role="alert" className="notice bad">{error}{result && ' Showing the previous check below.'}</p>}
            {result && <>
                <dl className="overview">
                    <div><dt>Starts (SAST)</dt><dd>{time(result.starts_at)}</dd></div>
                    <div><dt>Restores (SAST)</dt><dd>{time(result.ends_at)}</dd></div>
                    <div><dt>Scheduler state</dt><dd>{result.state?.replaceAll('_', ' ') ?? 'Not recorded'}</dd></div>
                    <div><dt>Last successful check (SAST)</dt><dd>{time(result.checked_at)}</dd></div>
                </dl>
                {(!result.enabled || !result.writes_enabled) && <p className="notice warning">
                    {[
                        !result.enabled && 'Promotion scheduler disabled',
                        !result.writes_enabled && 'WordPress writes disabled',
                    ].filter(Boolean).join('. ')}.
                </p>}
                <section aria-labelledby="fees-heading">
                    <div className="section-heading">
                        <h2 id="fees-heading">Live WordPress fees</h2>
                        <span className={problems ? 'bad' : verified ? 'good' : ''}>{summary}</span>
                    </div>
                    {result.rows.length === 0 ? <p>No promotion targets were returned.</p> :
                        <div className="table-scroll" role="region" aria-label="Booking fee comparison" tabIndex={0}>
                            <table>
                                <thead><tr><th scope="col">WordPress item</th><th scope="col">Current</th><th scope="col">Expected</th><th scope="col">Check</th></tr></thead>
                                <tbody>{result.rows.map((row) => <tr key={row.key}>
                                    <th scope="row">{row.title}<small>{row.field === 'booking_fee' ? 'Residence base fee' : 'Room override'}</small></th>
                                    <td>{row.result === 'missing' ? 'Unavailable' : fee(row.current)}</td>
                                    <td>{row.result === 'observed' ? '-' : fee(row.expected)}</td>
                                    <td className={row.result === 'match' ? 'good' : ['missing', 'mismatch'].includes(row.result) ? 'bad' : ''}>{labels[row.result]}</td>
                                </tr>)}</tbody>
                            </table>
                        </div>}
                </section>
            </>}
        </main>
        <footer>Campus Africa</footer>
    </>;
}

createRoot(root).render(<Checker />);
