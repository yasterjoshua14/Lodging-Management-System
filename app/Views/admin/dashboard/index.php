<?php

$dashboardGraphPayload = [
    'monthlyEarnings' => array_map(static fn (array $month): array => [
        'label'    => (string) $month['label'],
        'bookings' => (int) $month['bookings'],
        'amount'   => (float) $month['amount'],
        'percent'  => (float) $month['percent'],
    ], $monthlyEarnings),
    'revenueBreakdown' => array_map(static fn (array $item): array => [
        'status' => (string) $item['status'],
        'label'  => (string) $item['label'],
        'count'  => (int) $item['count'],
        'amount' => (float) $item['amount'],
    ], $revenueBreakdown),
    'bookingStatusBreakdown' => array_map(static fn (array $item): array => [
        'status' => (string) $item['status'],
        'label'  => (string) $item['label'],
        'count'  => (int) $item['count'],
        'amount' => (float) $item['amount'],
    ], $bookingStatusBreakdown),
    'roomsByStatus' => array_map(static fn (array $item): array => [
        'status' => (string) $item['status'],
        'label'  => (string) $item['label'],
        'count'  => (int) $item['count'],
    ], $roomsByStatus),
];

$dashboardGraphJson = json_encode(
    $dashboardGraphPayload,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
);

$dashboardGraphJson = $dashboardGraphJson === false ? 'null' : $dashboardGraphJson;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <style>
        .interactive-card {
            overflow: hidden;
        }

        .interactive-head {
            align-items: flex-start;
        }

        .toggle-group {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 6px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.55);
        }

        .graph-toggle {
            border: 0;
            padding: 10px 14px;
            border-radius: 999px;
            background: transparent;
            color: var(--muted);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .graph-toggle:hover,
        .graph-toggle:focus-visible,
        .column-bar:focus-visible,
        .legend-item:focus-visible {
            color: var(--accent-dark);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 94, 52, 0.22);
        }

        .graph-toggle.is-active {
            color: var(--accent-dark);
            background: linear-gradient(135deg, var(--accent-soft), rgba(255, 255, 255, 0.95));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.76);
        }

        .chart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            padding: 18px;
            border-radius: 20px;
            border: 1px solid rgba(221, 203, 185, 0.84);
            background: rgba(255, 255, 255, 0.46);
        }

        .chart-summary p,
        .summary-pill p {
            margin: 6px 0 0;
        }

        .chart-kicker {
            display: block;
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .chart-focus {
            display: block;
            margin: 6px 0 0;
            font-size: clamp(1.4rem, 3vw, 2rem);
            line-height: 1.08;
        }

        .summary-pill {
            min-width: 190px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.88);
            background: rgba(255, 255, 255, 0.82);
        }

        .summary-pill strong {
            display: block;
            margin-top: 6px;
            font-size: 1.05rem;
        }

        .chart-scroll {
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .column-chart {
            min-width: 470px;
            min-height: 316px;
            display: grid;
            grid-template-columns: repeat(6, minmax(64px, 1fr));
            gap: 14px;
            align-items: end;
        }

        .column-bar {
            border: 0;
            padding: 0;
            background: transparent;
            color: inherit;
            text-align: left;
            cursor: pointer;
            display: grid;
            gap: 10px;
            transition: opacity 0.2s ease;
        }

        .column-bar.is-dim {
            opacity: 0.7;
        }

        .column-bar-top {
            min-height: 2.3em;
            display: block;
            color: var(--accent-dark);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .column-bar-tower {
            min-height: 220px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .column-bar-rod {
            width: min(56px, 100%);
            height: var(--bar-height);
            min-height: 12px;
            border-radius: 18px 18px 10px 10px;
            background: linear-gradient(180deg, #c89b70 0%, var(--accent) 55%, var(--accent-dark) 100%);
            box-shadow: 0 16px 24px rgba(94, 59, 29, 0.16);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .column-bar.is-active .column-bar-rod,
        .column-bar:hover .column-bar-rod,
        .column-bar:focus-visible .column-bar-rod {
            transform: translateY(-4px);
            box-shadow: 0 22px 30px rgba(94, 59, 29, 0.22);
        }

        .column-bar-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .column-bar-note {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
        }

        .status-layout {
            display: grid;
            grid-template-columns: minmax(220px, 0.9fr) minmax(0, 1.1fr);
            gap: 22px;
            align-items: center;
        }

        .status-ring-panel {
            display: grid;
            place-items: center;
        }

        .status-ring-shell {
            position: relative;
            width: min(250px, 100%);
            aspect-ratio: 1;
        }

        .status-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: conic-gradient(var(--accent-soft) 0 100%);
            transition: transform 0.35s ease, filter 0.25s ease;
        }

        .status-ring::after {
            content: '';
            position: absolute;
            inset: 23%;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: inset 0 0 0 1px rgba(221, 203, 185, 0.72);
        }

        .status-ring-core {
            position: absolute;
            inset: 0;
            z-index: 1;
            display: grid;
            place-content: center;
            text-align: center;
            padding: 26%;
        }

        .status-ring-core p {
            margin: 6px 0 0;
        }

        .status-legend {
            display: grid;
            gap: 12px;
        }

        .legend-item {
            border: 1px solid rgba(221, 203, 185, 0.82);
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.46);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 12px;
            text-align: left;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .legend-item.is-active {
            transform: translateY(-1px);
            border-color: rgba(94, 59, 29, 0.28);
            background: rgba(255, 255, 255, 0.82);
        }

        .legend-swatch {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--swatch);
            box-shadow: 0 0 0 5px var(--swatch-soft);
        }

        .legend-copy {
            display: grid;
            gap: 4px;
        }

        .legend-copy strong,
        .legend-copy span,
        .legend-value {
            margin: 0;
        }

        .legend-copy span {
            color: var(--muted);
            font-size: 0.84rem;
        }

        .legend-value {
            font-weight: 700;
            white-space: nowrap;
        }

        .graph-empty {
            padding: 32px 18px;
            border: 1px dashed var(--line);
            border-radius: 20px;
            text-align: center;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.36);
        }

        @media (max-width: 920px) {
            .status-layout {
                grid-template-columns: 1fr;
            }

            .chart-summary {
                flex-direction: column;
                align-items: stretch;
            }

            .summary-pill {
                min-width: 0;
            }
        }

        @media (max-width: 640px) {
            .interactive-head {
                flex-direction: column;
                align-items: stretch;
            }

            .toggle-group {
                width: 100%;
            }

            .graph-toggle {
                flex: 1 1 auto;
                text-align: center;
            }

            .column-chart {
                min-width: 420px;
            }

            .legend-item {
                grid-template-columns: auto 1fr;
            }

            .legend-value {
                grid-column: 2;
            }
        }
    </style>

    <div class="section-head">
        <div>
            <h2>Operations Dashboard</h2>
            <p>Focus on monthly revenue movement, status distribution, and booking activity.</p>
        </div>
    </div>

    <section class="split-grid">
        <article class="card interactive-card">
            <div class="list-head interactive-head">
                <div>
                    <h2>Revenue Trend Explorer</h2>
                    <p>Switch between booked revenue and booking volume for each month.</p>
                </div>
                <div class="toggle-group" aria-label="Revenue trend metric">
                    <button type="button" class="graph-toggle is-active" data-trend-metric="amount" aria-pressed="true">Revenue</button>
                    <button type="button" class="graph-toggle" data-trend-metric="bookings" aria-pressed="false">Bookings</button>
                </div>
            </div>

            <div class="chart-summary">
                <div>
                    <span class="chart-kicker" data-trend-label>Selected revenue</span>
                    <strong class="chart-focus" data-trend-primary>Loading...</strong>
                    <p class="text-muted" data-trend-secondary>Preparing trend data.</p>
                </div>
                <div class="summary-pill">
                    <span class="chart-kicker" data-trend-peak-label>Peak revenue</span>
                    <strong data-trend-peak>Loading...</strong>
                    <p class="text-muted">Based on the visible metric.</p>
                </div>
            </div>

            <div class="chart-scroll">
                <div class="column-chart" id="trend-chart"></div>
            </div>

            <noscript>
                <div class="graph-empty">Enable JavaScript to interact with the monthly trend chart.</div>
            </noscript>
        </article>

        <article class="card interactive-card">
            <div class="list-head interactive-head">
                <div>
                    <h2>Status Explorer</h2>
                    <p>Compare revenue pipeline, booking stages, and room inventory from one graph.</p>
                </div>
                <div class="toggle-group" aria-label="Status explorer mode">
                    <button type="button" class="graph-toggle is-active" data-status-mode="revenue" aria-pressed="true">Revenue</button>
                    <button type="button" class="graph-toggle" data-status-mode="bookings" aria-pressed="false">Bookings</button>
                    <button type="button" class="graph-toggle" data-status-mode="rooms" aria-pressed="false">Rooms</button>
                </div>
            </div>

            <div class="status-layout">
                <div class="status-ring-panel">
                    <div class="status-ring-shell">
                        <div class="status-ring" id="status-ring"></div>
                        <div class="status-ring-core">
                            <span class="chart-kicker" data-status-total-label>Total value</span>
                            <strong class="chart-focus" data-status-total>Loading...</strong>
                            <p class="text-muted" data-status-selected>Preparing status data.</p>
                        </div>
                    </div>
                </div>

                <div class="status-legend" id="status-legend"></div>
            </div>

            <noscript>
                <div class="graph-empty">Enable JavaScript to use the status explorer.</div>
            </noscript>
        </article>
    </section>

    <section class="card" style="margin-top: 22px;">
        <div class="list-head">
            <div>
                <h2>Recent Bookings</h2>
                <p>Most recent booking activity captured by the system.</p>
            </div>
            <a href="<?= esc(admin_path('bookings')) ?>" class="link-inline">Manage bookings</a>
        </div>

        <?php if ($recentBookings !== []): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Room</th>
                            <th>Stay</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?= esc($booking['tenant_name']) ?></td>
                                <td>
                                    <strong><?= esc($booking['room_number']) ?></strong><br>
                                    <span class="text-muted"><?= esc(humanize_key($booking['room_type'])) ?></span>
                                </td>
                                <td><?= esc($booking['check_in']) ?> to <?= esc($booking['check_out']) ?></td>
                                <td><span class="<?= esc(status_badge_class($booking['status'])) ?>"><?= esc(booking_status_options()[$booking['status']] ?? humanize_key($booking['status'])) ?></span></td>
                                <td><?= esc(format_money($booking['total_amount'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No bookings recorded</h3>
                <p class="text-muted">Booking activity will appear here as soon as the first stay is saved.</p>
            </div>
        <?php endif; ?>
    </section>

    <script>
        (() => {
            const analytics = <?= $dashboardGraphJson ?>;

            if (!analytics) {
                return;
            }

            const moneyFormatter = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const compactMoneyFormatter = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                notation: 'compact',
                maximumFractionDigits: 1,
            });

            const statusColors = {
                pending: { solid: '#8b5e34', soft: '#efdfcd' },
                checked_in: { solid: '#ae7320', soft: '#faebd4' },
                checked_out: { solid: '#2d7a5c', soft: '#d7eee4' },
                cancelled: { solid: '#b14b45', soft: '#f6d8d5' },
                available: { solid: '#2d7a5c', soft: '#d7eee4' },
                occupied: { solid: '#ae7320', soft: '#faebd4' },
                maintenance: { solid: '#6b7280', soft: '#e5e7eb' },
            };

            const state = {
                trendMetric: 'amount',
                trendActive: Math.max(analytics.monthlyEarnings.length - 1, 0),
                statusMode: 'revenue',
                statusActive: 0,
            };

            const trendButtons = Array.from(document.querySelectorAll('[data-trend-metric]'));
            const statusButtons = Array.from(document.querySelectorAll('[data-status-mode]'));

            const trendChart = document.getElementById('trend-chart');
            const trendLabel = document.querySelector('[data-trend-label]');
            const trendPrimary = document.querySelector('[data-trend-primary]');
            const trendSecondary = document.querySelector('[data-trend-secondary]');
            const trendPeakLabel = document.querySelector('[data-trend-peak-label]');
            const trendPeak = document.querySelector('[data-trend-peak]');

            const statusRing = document.getElementById('status-ring');
            const statusLegend = document.getElementById('status-legend');
            const statusTotalLabel = document.querySelector('[data-status-total-label]');
            const statusTotal = document.querySelector('[data-status-total]');
            const statusSelected = document.querySelector('[data-status-selected]');

            const trendMetricConfig = {
                amount: {
                    label: 'Selected revenue',
                    peakLabel: 'Peak revenue',
                    value: (item) => Number(item.amount || 0),
                    topValue: (item) => formatCompactMoney(item.amount || 0),
                    summaryValue: (item) => formatMoney(item.amount || 0),
                    secondary: (item) => `${item.label} - ${formatCount(item.bookings, 'booking')} scheduled`,
                    note: (item) => `${item.bookings} bk`,
                    peakValue: (item) => `${item.label} - ${formatCompactMoney(item.amount || 0)}`,
                },
                bookings: {
                    label: 'Selected booking volume',
                    peakLabel: 'Peak booking volume',
                    value: (item) => Number(item.bookings || 0),
                    topValue: (item) => Number(item.bookings || 0).toLocaleString(),
                    summaryValue: (item) => formatCount(item.bookings, 'booking'),
                    secondary: (item) => `${item.label} - ${formatMoney(item.amount || 0)} booked revenue`,
                    note: (item) => formatCompactMoney(item.amount || 0),
                    peakValue: (item) => `${item.label} - ${formatCount(item.bookings, 'booking')}`,
                },
            };

            const statusModeConfig = {
                revenue: {
                    items: analytics.revenueBreakdown,
                    totalLabel: 'Total pipeline value',
                    value: (item) => Number(item.amount || 0),
                    valueText: (item) => formatMoney(item.amount || 0),
                    totalText: (total) => formatMoney(total),
                    secondary: (item) => formatCount(item.count, 'booking'),
                    selectedText: (item, share) => `${item.label} - ${formatMoney(item.amount || 0)} - ${share.toFixed(1)}%`,
                },
                bookings: {
                    items: analytics.bookingStatusBreakdown,
                    totalLabel: 'Total bookings',
                    value: (item) => Number(item.count || 0),
                    valueText: (item) => formatCount(item.count, 'booking'),
                    totalText: (total) => formatCount(total, 'booking'),
                    secondary: (item) => formatMoney(item.amount || 0),
                    selectedText: (item, share) => `${item.label} - ${formatCount(item.count, 'booking')} - ${share.toFixed(1)}%`,
                },
                rooms: {
                    items: analytics.roomsByStatus,
                    totalLabel: 'Room inventory',
                    value: (item) => Number(item.count || 0),
                    valueText: (item) => `${Number(item.count || 0).toLocaleString()} rooms`,
                    totalText: (total) => `${Number(total).toLocaleString()} rooms`,
                    secondary: (item, total) => total > 0 ? `${((Number(item.count || 0) / total) * 100).toFixed(1)}% of rooms` : '0.0% of rooms',
                    selectedText: (item, share) => `${item.label} - ${Number(item.count || 0).toLocaleString()} rooms - ${share.toFixed(1)}%`,
                },
            };

            function formatMoney(value) {
                return moneyFormatter.format(Number(value || 0));
            }

            function formatCompactMoney(value) {
                return compactMoneyFormatter.format(Number(value || 0));
            }

            function formatCount(value, noun) {
                const count = Number(value || 0);

                return `${count.toLocaleString()} ${noun}${count === 1 ? '' : 's'}`;
            }

            function getStatusColor(status) {
                return statusColors[status] ?? { solid: '#8b5e34', soft: '#efdfcd' };
            }

            function hexToRgba(hex, alpha) {
                const normalized = hex.replace('#', '');
                const bigint = parseInt(normalized, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;

                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            function syncToggleButtons(buttons, activeValue, dataKey) {
                buttons.forEach((button) => {
                    const isActive = button.dataset[dataKey] === activeValue;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            }

            function getPeakItem(items, metricGetter) {
                if (items.length === 0) {
                    return null;
                }

                return items.reduce((best, current) => (
                    metricGetter(current) > metricGetter(best) ? current : best
                ));
            }

            function getPeakIndex(items, metricGetter) {
                if (items.length === 0) {
                    return 0;
                }

                let peakIndex = 0;

                items.forEach((item, index) => {
                    if (metricGetter(item) > metricGetter(items[peakIndex])) {
                        peakIndex = index;
                    }
                });

                return peakIndex;
            }

            function renderTrendChart() {
                const items = analytics.monthlyEarnings;
                const config = trendMetricConfig[state.trendMetric];

                syncToggleButtons(trendButtons, state.trendMetric, 'trendMetric');

                if (items.length === 0) {
                    trendChart.innerHTML = '<div class="graph-empty">No monthly trend data available yet.</div>';
                    trendLabel.textContent = config.label;
                    trendPrimary.textContent = 'No data';
                    trendSecondary.textContent = 'Add more bookings to unlock the trend chart.';
                    trendPeakLabel.textContent = config.peakLabel;
                    trendPeak.textContent = 'No data';
                    return;
                }

                if (state.trendActive >= items.length) {
                    state.trendActive = items.length - 1;
                }

                const peakValue = Math.max(...items.map((item) => config.value(item)), 0);

                trendChart.innerHTML = items.map((item, index) => {
                    const value = config.value(item);
                    const height = peakValue > 0 ? Math.max((value / peakValue) * 100, 8) : 8;

                    return `
                        <button type="button" class="column-bar ${index === state.trendActive ? 'is-active' : 'is-dim'}" data-trend-index="${index}">
                            <span class="column-bar-top">${config.topValue(item)}</span>
                            <span class="column-bar-tower">
                                <span class="column-bar-rod" style="--bar-height: ${height.toFixed(1)}%;"></span>
                            </span>
                            <span class="column-bar-label">${item.label}</span>
                            <span class="column-bar-note">${config.note(item)}</span>
                        </button>
                    `;
                }).join('');

                trendChart.querySelectorAll('[data-trend-index]').forEach((button) => {
                    button.addEventListener('click', () => {
                        state.trendActive = Number(button.dataset.trendIndex);
                        renderTrendChart();
                    });
                });

                const selectedItem = items[state.trendActive];
                const peakItem = getPeakItem(items, (item) => config.value(item));

                trendLabel.textContent = config.label;
                trendPrimary.textContent = config.summaryValue(selectedItem);
                trendSecondary.textContent = config.secondary(selectedItem);
                trendPeakLabel.textContent = config.peakLabel;
                trendPeak.textContent = peakItem ? config.peakValue(peakItem) : 'No data';
            }

            function renderStatusExplorer() {
                const config = statusModeConfig[state.statusMode];
                const items = config.items;

                syncToggleButtons(statusButtons, state.statusMode, 'statusMode');

                if (items.length === 0) {
                    statusRing.style.background = 'conic-gradient(var(--accent-soft) 0 100%)';
                    statusRing.style.transform = 'rotate(-90deg)';
                    statusRing.style.filter = 'none';
                    statusLegend.innerHTML = '<div class="graph-empty">No status data available yet.</div>';
                    statusTotalLabel.textContent = config.totalLabel;
                    statusTotal.textContent = 'No data';
                    statusSelected.textContent = 'Save bookings and rooms to populate this graph.';
                    return;
                }

                const total = items.reduce((sum, item) => sum + config.value(item), 0);
                if (state.statusActive >= items.length) {
                    state.statusActive = 0;
                }

                let cursor = 0;
                const segments = items.map((item) => {
                    const value = config.value(item);
                    const share = total > 0 ? (value / total) * 100 : 0;
                    const start = cursor;
                    cursor += share;

                    return {
                        ...item,
                        value,
                        share,
                        start,
                        end: cursor,
                        colors: getStatusColor(item.status),
                    };
                });

                const selected = segments[state.statusActive] ?? segments[0];
                const midpoint = selected ? (selected.start + selected.end) / 2 : 0;
                const gradient = segments.some((segment) => segment.value > 0)
                    ? segments.map((segment) => `${segment.colors.solid} ${segment.start.toFixed(2)}% ${segment.end.toFixed(2)}%`).join(', ')
                    : 'var(--accent-soft) 0 100%';

                statusRing.style.background = `conic-gradient(${gradient})`;
                statusRing.style.transform = `rotate(${-90 - (midpoint * 3.6)}deg)`;
                statusRing.style.filter = selected ? `drop-shadow(0 18px 28px ${hexToRgba(selected.colors.solid, 0.22)})` : 'none';

                statusLegend.innerHTML = segments.map((segment, index) => `
                    <button type="button" class="legend-item ${index === state.statusActive ? 'is-active' : ''}" data-status-index="${index}">
                        <span class="legend-swatch" style="--swatch: ${segment.colors.solid}; --swatch-soft: ${segment.colors.soft};"></span>
                        <span class="legend-copy">
                            <strong>${segment.label}</strong>
                            <span>${config.secondary(segment, total)}</span>
                        </span>
                        <strong class="legend-value">${config.valueText(segment)}</strong>
                    </button>
                `).join('');

                statusLegend.querySelectorAll('[data-status-index]').forEach((button) => {
                    button.addEventListener('click', () => {
                        state.statusActive = Number(button.dataset.statusIndex);
                        renderStatusExplorer();
                    });
                });

                statusTotalLabel.textContent = config.totalLabel;
                statusTotal.textContent = config.totalText(total);
                statusSelected.textContent = selected
                    ? config.selectedText(selected, selected.share)
                    : 'No data available.';
            }

            trendButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    state.trendMetric = button.dataset.trendMetric || 'amount';
                    renderTrendChart();
                });
            });

            statusButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    state.statusMode = button.dataset.statusMode || 'revenue';
                    const nextConfig = statusModeConfig[state.statusMode];
                    state.statusActive = getPeakIndex(nextConfig.items, nextConfig.value);
                    renderStatusExplorer();
                });
            });

            state.statusActive = getPeakIndex(statusModeConfig[state.statusMode].items, statusModeConfig[state.statusMode].value);
            renderTrendChart();
            renderStatusExplorer();
        })();
    </script>
<?= $this->endSection() ?>
