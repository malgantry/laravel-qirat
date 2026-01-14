import './bootstrap';
import Chart from 'chart.js/auto';

const dashboardData = window.dashboardData || {};
const palette = {
	'طعام': '#F59E0B','تسوق': '#8B5CF6','فواتير': '#EF4444','ترفيه': '#3B82F6','هاتف': '#06B6D4','رياضة': '#10B981','تجميل': '#EC4899','تعليم': '#22C55E','اجتماعي': '#6366F1','راتب': '#0EA5E9','مكافأة': '#F43F5E','استثمار': '#34D399','تحويل': '#64748B'
};

const htmlEl = document.documentElement;
const bodyEl = document.body;
const themeStorageKey = 'qiratae-theme';
const langStorageKey = 'qiratae-lang';

const i18n = {
	ar: {
		appName: 'قيراط المالي',
		appTagline: 'تحكم كامل بمدخراتك',
		langAr: 'عربي',
		langEn: 'English',
		language: 'اللغة',
		appearance: 'المظهر',
		light: 'فاتح',
		dark: 'داكن',
		home: 'الرئيسية',
		transactions: 'المعاملات',
		goals: 'الأهداف',
		stats: 'الإحصائيات',
		profile: 'الملف',
		settings: 'الإعدادات',
	},
	en: {
		appName: 'Qiratae Finance',
		appTagline: 'Full control of your savings',
		langAr: 'Arabic',
		langEn: 'English',
		language: 'Language',
		appearance: 'Appearance',
		light: 'Light',
		dark: 'Dark',
		home: 'Home',
		transactions: 'Transactions',
		goals: 'Goals',
		stats: 'Statistics',
		profile: 'Profile',
		settings: 'Settings',
},
};

const applyTheme = (theme) => {
	const safeTheme = theme === 'dark' ? 'dark' : 'light';
	htmlEl.dataset.theme = safeTheme;
	const toggle = document.getElementById('themeToggle');
	if (toggle) {
		const label = toggle.querySelector('.btn-label');
		const icon = toggle.querySelector('i');
		if (label) label.textContent = safeTheme === 'dark' ? i18n[currentLang()].dark : i18n[currentLang()].light;
		if (icon) icon.className = safeTheme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-brightness-high';
	}
	localStorage.setItem(themeStorageKey, safeTheme);
};

const applyLanguage = (lang) => {
	const safeLang = lang === 'en' ? 'en' : 'ar';
	bodyEl.dir = safeLang === 'en' ? 'ltr' : 'rtl';
	htmlEl.lang = safeLang === 'en' ? 'en' : 'ar';
	document.querySelectorAll('[data-i18n]').forEach((el) => {
		const key = el.dataset.i18n;
		if (key && i18n[safeLang]?.[key]) {
			el.textContent = i18n[safeLang][key];
		}
	});
	const toggle = document.getElementById('langToggle');
	if (toggle) {
		const label = toggle.querySelector('.btn-label');
		const dictionary = i18n[safeLang];
		if (label) label.textContent = safeLang === 'en' ? dictionary.langEn : dictionary.langAr;
	}
	localStorage.setItem(langStorageKey, safeLang);
};

// Expose toggles for settings page buttons
window.applyTheme = applyTheme;
window.applyLanguage = applyLanguage;

const currentLang = () => localStorage.getItem(langStorageKey) || 'ar';
const currentTheme = () => localStorage.getItem(themeStorageKey) || 'light';

const bootstrapToggles = () => {
	applyLanguage(currentLang());
	applyTheme(currentTheme());

	const themeToggle = document.getElementById('themeToggle');
	themeToggle?.addEventListener('click', () => {
		const next = currentTheme() === 'dark' ? 'light' : 'dark';
		applyTheme(next);
	});

	const langToggle = document.getElementById('langToggle');
	langToggle?.addEventListener('click', () => {
		const next = currentLang() === 'en' ? 'ar' : 'en';
		applyLanguage(next);
		applyTheme(currentTheme());
	});
};

const showToast = (type = 'info', message = '') => {
	const area = document.getElementById('toast-area');
	if (!area) return;
	const div = document.createElement('div');
	div.className = `toast-chip ${type}`;
	div.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-x-circle' : 'bi-info-circle'}"></i><span>${message}</span>`;
	area.appendChild(div);
	setTimeout(() => div.remove(), 3200);
};

window.qirataeToast = showToast;

// Helpers for theme-aware charts
const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
const withAlpha = (hex, alpha = 0.2) => {
	// supports #rrggbb or rgb(a)
	if (!hex) return `rgba(0,0,0,${alpha})`;
	if (hex.startsWith('rgb')) {
		const parts = hex.replace(/rgba?\(|\)/g, '').split(',').map(p=>p.trim());
		const [r,g,b] = parts;
		return `rgba(${r}, ${g}, ${b}, ${alpha})`;
	}
	const h = hex.replace('#','');
	const r = parseInt(h.substring(0,2), 16);
	const g = parseInt(h.substring(2,4), 16);
	const b = parseInt(h.substring(4,6), 16);
	return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const themeChartOptions = () => {
	const text = cssVar('--text-secondary') || '#64748b';
	const primary = cssVar('--text-primary') || '#0f172a';
	const grid = cssVar('--card-border') || '#e5e7eb';
	const tooltipBg = cssVar('--card-bg') || '#ffffff';
	return {
		scales: {
			x: { grid: { color: withAlpha(grid, 0.25) }, ticks: { color: text } },
			y: { grid: { color: withAlpha(grid, 0.25) }, ticks: { color: text } },
		},
		plugins: {
			legend: { labels: { color: text } },
			tooltip: {
				backgroundColor: tooltipBg,
				titleColor: primary,
				bodyColor: text,
				borderColor: withAlpha(grid, 0.6),
				borderWidth: 1,
			},
		},
	};
};

const chartStore = {
	instances: {},
	upsert(id, chart) { if (this.instances[id]) this.instances[id].destroy(); this.instances[id] = chart; },
	destroyAll() { Object.values(this.instances).forEach(c=>c.destroy()); this.instances = {}; },
};

const renderBar = () => {
	const canvas = document.getElementById('monthlyChart');
	if (!canvas) return;

	const months = dashboardData.months || [];
	const income = dashboardData.income || [];
	const expense = dashboardData.expense || [];

	if (!months.length) {
		canvas.classList.add('d-none');
		document.getElementById('monthlyEmpty')?.classList.remove('d-none');
		return;
	}

	canvas.classList.remove('d-none');
	document.getElementById('monthlyEmpty')?.classList.add('d-none');

	const options = themeChartOptions();
	const incomeColor = '#c9a227'; // golden
	const expenseColor = '#d35400'; // terracotta
	const ctx = canvas.getContext('2d');
	const gradIncome = ctx.createLinearGradient(0, 0, 0, canvas.height || 300);
	gradIncome.addColorStop(0, withAlpha(incomeColor, 0.35));
	gradIncome.addColorStop(1, withAlpha(incomeColor, 0.02));
	const gradExpense = ctx.createLinearGradient(0, 0, 0, canvas.height || 300);
	gradExpense.addColorStop(0, withAlpha(expenseColor, 0.28));
	gradExpense.addColorStop(1, withAlpha(expenseColor, 0.02));

	const chart = new Chart(canvas, {
		type: 'line',
		data: {
			labels: months,
			datasets: [
				{
					label: 'الدخل',
					data: income,
					borderColor: incomeColor,
					backgroundColor: gradIncome,
					fill: true,
					tension: 0.35,
					borderWidth: 2,
					pointRadius: 3.5,
					pointBackgroundColor: incomeColor,
					pointBorderColor: '#0b0f16',
				},
				{
					label: 'المصروف',
					data: expense,
					borderColor: expenseColor,
					backgroundColor: gradExpense,
					fill: true,
					tension: 0.35,
					borderWidth: 2,
					pointRadius: 3.5,
					pointBackgroundColor: expenseColor,
					pointBorderColor: '#0b0f16',
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			...options,
		},
	});
	chartStore.upsert('monthlyChart', chart);
};

const renderPie = () => {
	const canvas = document.getElementById('categoryChart');
	if (!canvas) return;

	const categories = dashboardData.categories || [];
	const totals = dashboardData.categoryTotals || [];

	if (!categories.length) {
		canvas.classList.add('d-none');
		document.getElementById('categoryEmpty')?.classList.remove('d-none');
		return;
	}

	const finsmart = document.documentElement.getAttribute('data-style') === 'finsmart';
	const fallback = ['#c9a227','#d59b4e','#b07f2f','#d35400','#4e5a3d','#4a6572','#e6b450','#9c6b3b','#7a6b5a'];
	const colors = (categories || []).map((c, i) => palette[c] || fallback[i % fallback.length]);
	const options = themeChartOptions();
	const chart = new Chart(canvas, {
		type: 'pie',
		data: {
			labels: categories,
			datasets: [
				{
					label: 'المصروفات حسب الفئة',
					data: totals,
					backgroundColor: colors,
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			...options,
		},
	});
	chartStore.upsert('categoryChart', chart);
};

const renderHomeExpensePie = () => {
	const canvas = document.getElementById('homeExpensePie');
	if (!canvas) return;

	const categories = dashboardData.categories || [];
	const totals = dashboardData.categoryTotals || [];

	if (!categories.length) {
		canvas.classList.add('d-none');
		document.getElementById('homeExpenseEmpty')?.classList.remove('d-none');
		return;
	}

	canvas.classList.remove('d-none');
	document.getElementById('homeExpenseEmpty')?.classList.add('d-none');

	const fallback = ['#c9a227','#d59b4e','#b07f2f','#d35400','#4e5a3d','#4a6572','#e6b450','#9c6b3b','#7a6b5a'];
	const colors = categories.map((c, i) => palette[c] || fallback[i % fallback.length]);
	const base = themeChartOptions();
	const options = {
		...base,
		plugins: {
			...base.plugins,
			legend: { ...base.plugins.legend, position: 'bottom' },
		},
	};
	const chart = new Chart(canvas, {
		type: 'pie',
		data: {
			labels: categories,
			datasets: [{
				label: 'المصروفات',
				data: totals,
				backgroundColor: colors,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			...options,
		},
	});
	chartStore.upsert('homeExpensePie', chart);
};

document.addEventListener('DOMContentLoaded', () => {
	bootstrapToggles();
	renderBar();
	renderPie();
		renderHomeExpensePie();
	// Re-render charts when theme/style changes
	const obs = new MutationObserver((mut) => {
		if (mut.some(m => m.type === 'attributes' && (m.attributeName === 'data-theme' || m.attributeName === 'data-style'))) {
			renderBar();
			renderPie();
				renderHomeExpensePie();
		}
	});
	obs.observe(document.documentElement, { attributes: true });
});

// Shared: show/hide password toggles across auth forms
const attachPasswordToggles = () => {
	document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
		const targetId = btn.getAttribute('data-toggle-password');
		const input = document.getElementById(targetId);
		if (!input) return;

		btn.addEventListener('click', () => {
			const isHidden = input.type === 'password';
			input.type = isHidden ? 'text' : 'password';
			const icon = btn.querySelector('i');
			if (icon) icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
			const label = btn.querySelector('.toggle-text');
			if (label) label.textContent = isHidden ? 'إخفاء' : 'إظهار';
		});
	});
};

document.addEventListener('DOMContentLoaded', attachPasswordToggles);
