import './bootstrap';
import Chart from 'chart.js/auto';

const dashboardData = window.dashboardData || {};
const palette = {
	'طعام': '#F59E0B', 'تسوق': '#8B5CF6', 'فواتير': '#EF4444', 'ترفيه': '#3B82F6', 'هاتف': '#06B6D4', 'رياضة': '#10B981', 'تجميل': '#EC4899', 'تعليم': '#22C55E', 'اجتماعي': '#6366F1', 'راتب': '#0EA5E9', 'مكافأة': '#F43F5E', 'استثمار': '#34D399', 'تحويل': '#64748B'
};

const htmlEl = document.documentElement;
const bodyEl = document.body;
const themeStorageKey = 'qiratae-theme';
const langStorageKey = 'qiratae-lang';

const statusMappings = {
	'تم حفظ الهدف بنجاح': 'statusGoalSaved',
	'تم تحديث الهدف بنجاح': 'statusGoalUpdated',
	'تم حذف الهدف': 'statusGoalDeleted',
	'تم إنشاء الميزانية بنجاح': 'statusBudgetSaved',
	'تم تحديث الميزانية بنجاح': 'statusBudgetUpdated',
	'تم حذف الميزانية': 'statusBudgetDeleted',
	'تم حفظ المعاملة بنجاح': 'statusTransactionSaved',
	'تم تحديث المعاملة بنجاح': 'statusTransactionUpdated',
	'تم حذف المعاملة': 'statusTransactionDeleted',
	'تم تحديث الملف الشخصي بنجاح': 'statusProfileUpdated',
	'تم تفعيل المستخدم.': 'statusUserActivated',
	'تم تعطيل المستخدم.': 'statusUserDisabled',
	'تم منح صلاحية مدير النظام.': 'statusAdminPromoted',
	'تم إزالة صلاحية مدير النظام.': 'statusAdminStripped',
	'تم إرسال رابط إعادة التعيين إلى بريد المستخدم.': 'statusResetSent',
};

const i18n = {
	ar: {
		appName: 'قيراط',
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
		reports: 'التقارير',
		stats: 'الإحصائيات',
		profile: 'الملف',
		settings: 'الإعدادات',
		logout: 'تسجيل الخروج',
		login: 'تسجيل الدخول',
		register: 'إنشاء حساب',
		welcome: 'مرحباً بك',
		premiumVersion: 'النسخة الفاخرة',
		dashboardTagline: 'لوحة التحكم المالية الخاصة بك، بتصميم عصري وأدوات متطورة.',
		transaction: 'معاملة',
		goal: 'هدف',
		currentBalance: 'الرصيد الحالي',
		income: 'دخل',
		expense: 'مصروف',
		analyzing: 'جاري التحليل...',
		basedOnSpending: 'بناءً على نمط إنفاقك الأخير',
		expenseDistribution: 'توزيع المصروفات',
		topExpenseAnalysis: 'تحليل الفئات الأكثر إنفاقاً',
		noData: 'لا توجد بيانات',
		financialGoals: 'الأهداف المالية',
		viewAll: 'عرض الكل',
		secureFinance: 'إدارة مالية آمنة • خوارزميات متقدمة',
		controlPower: 'قوة السيطرة',
		onFuture: 'على مستقبلك المالي',
		landingDescription: 'تتبّع معاملاتك بدقة متناهية، حدّد أهداف الادخار، واطّلع على تحليلات متقدمة بتصميم يجسد الفخامة والوضوح.',
		fullyCompatible: 'متوافق كلياً',
		premiumInterfaces: 'واجهات فاخرة',
		proReports: 'تقارير احترافية',
		smartWallet: 'المحفظة الاحترافية',
		overview: 'نظرة عامة',
		growthRate: 'معدل النمو',
		homeGoal: 'هدف شراء منزل',
		welcomeBack: 'مرحباً بك مجدداً في قيراط',
		email: 'البريد الإلكتروني',
		password: 'كلمة المرور',
		rememberMe: 'تذكرني',
		forgotPassword: 'نسيت كلمة المرور؟',
		enter: 'دخول',
		noAccount: 'ليس لديك حساب؟',
		backToHome: 'عودة للرئيسية',
		notifications: 'التنبيهات',
		markAllRead: 'تحديد الكل كمقروء',
		noNotifications: 'لا توجد تنبيهات جديدة',
		viewAllNotifications: 'عرض كل التنبيهات',
		myAccount: 'حسابي',
		adminPanel: 'لوحة المدير',
		adminDashboard: 'لوحة المدير',
		users: 'المستخدمون',
		loginLogs: 'سجلات الدخول',
		registerJoin: 'سجّل بياناتك للانضمام وإدارة أموالك في قيراط',
		name: 'الاسم',
		fullName: 'الاسم الكامل',
		confirmPassword: 'تأكيد كلمة المرور',
		signUp: 'تسجيل جديد',
		haveAccount: 'لديك حساب بالفعل؟',
		footerText: 'قيراط - إدارة مالية عربية مبنية بـ Laravel + Bootstrap • v2.4 Platinum',
		back: 'رجوع',
		activeGoals: 'الأهداف النشطة',
		newGoal: 'هدف جديد',
		noGoalsYet: 'لا توجد أهداف بعد',
		startAddingGoal: 'ابدأ بإضافة هدف ادخار أو شراء وسيظهر تقدمك هنا بشكل أنيق.',
		createGoal: 'إنشاء هدف',
		edit: 'تعديل',
		delete: 'حذف',
		collected: 'تم جمع:',
		left: 'المتبقي:',
		transactionHistory: 'سجل المعاملات',
		newTransaction: 'معاملة جديدة',
		searchPlaceholder: 'بحث في الوصف أو الفئة',
		clear: 'مسح',
		all: 'الكل',
		noTransactionsYet: 'لا توجد معاملات بعد',
		startAddingTransactions: 'ابدأ بإضافة الفئات الأساسية ثم سجّل أول عملية دخل أو مصروف.',
		addTransaction: 'إضافة معاملة',
		viewBudgets: 'عرض الميزانيات',
		businessIntelligence: 'استخبارات الأعمال المالية',
		analyticalReports: 'التقارير التحليلية',
		exportData: 'تصدير البيانات',
		txtFormat: 'تنسيق نصي',
		excelFormat: 'Excel (احترافي)',
		pdfFormat: 'PDF (جاهز للطباعة)',
		financialPlanning: 'التخطيط المالي',
		searchTransactionsPlaceholder: 'البحث في المعاملات أو الفئات...',
		currentMonth: 'الشهر الحالي',
		'30days': '30 يوماً',
		quarterly: 'الربع السنوي',
		currentYear: 'العام الحالي',
		totalIncomeLabel: 'إجمالي الدخل',
		totalExpenseLabel: 'إجمالي المصروف',
		netBalance: 'الرصيد الصافي',
		savingsRate: 'معدل الادخار',
		analyticalAnalysis: 'تحليل نسبي للإنفاق حسب الفئات الرئيسية.',
		noDataPeriod: 'لا توجد بيانات مصروفات للفترة المحددة.',
		budgets: 'الميزانيات',
		newBudget: 'ميزانية جديدة',
		noBudgetsYet: 'لا توجد ميزانيات بعد',
		addCategoryLimit: 'أضف فئة ثم حدد حد شهري لمراقبة المصروف والالتزام بالخطة.',
		createBudget: 'إنشاء ميزانية',
		spent: 'المصروف',
		active: 'نشط',
		completed: 'مكتمل',
		accountSettings: 'إعدادات الحساب',
		updatePersonalInfo: 'قم بتحديث معلوماتك الشخصية وصورة الملف.',
		adminLabel: 'مدير النظام',
		fullNameLabel: 'الاسم الكامل',
		displayNamePlaceholder: 'اسم العرض المميز',
		emailDisplayOnly: 'البريد الإلكتروني (للعرض فقط)',
		emailChangeInfo: 'يتم تغيير البريد من الإعدادات الأمنية المتقدمة.',
		saveChanges: 'حفظ التغييرات',
		advancedSettings: 'الإعدادات المتقدمة',
		securityDescription: 'تحكم في ظهور بياناتك وسياسة خصوصية الذكاء الاصطناعي.',
		aiTrainingNotice: 'عند تفعيل الوضع العام، سيتم استغلال بياناتك (بشكل مغفل) في تدريب النموذج لتحسين دقة النصائح.',
		sharingEnabled: 'تم تفعيل مشاركة البيانات',
		privacyFull: 'تم تفعيل الخصوصية الكاملة',
		adminDashboardDesc: 'إدارة النظام والأنشطة.',
		manageUsersDesc: 'تفعيل الصلاحيات والحسابات.',
		reportsDesc: 'تحليل مالي مفصل للإنفاق.',
		goalsDesc: 'متابعة تقدم ادخارك المخطط.',
		settingsDesc: 'تعديل العملة وواجهة النظام.',
		quickAccessTools: 'الوصول السريع للأدوات',
		settingsPanel: 'لوحة الإعدادات',
		fullSystemControl: 'تحكم كامل في مظهر النظام والخيارات المتقدمة.',
		preferredLanguage: 'اللغة المفضلة',
		uiInterface: 'واجهة المستخدم',
		lightMode: 'وضع فاتح',
		darkMode: 'وضع داكن',
		defaultCurrency: 'العملة الافتراضية',
		strategicNavigation: 'التنقل الاستراتيجي',
		manageUsers: 'إدارة المستخدمين',
		lyd: 'الدينار الليبي (د.ل)',
		usd: 'الدولار الأمريكي ($)',
		eur: 'اليورو (€)',
		details: 'عرض التفاصيل',
		noNotificationsPage: 'لا توجد تنبيهات',
		latestActivityInfo: 'سنقوم بإعلامك بأحدث النشاطات هنا.',
		notification: 'تنبيه',
		reportsHeader: 'لوحة التقارير والتحليلات',
		reportsTitle: 'التقارير',
		instantSearch: 'بحث فوري أو اكتب لتحليل',
		monthly: 'شهري',
		weekly: 'أسبوعي',
		daily: 'يومي',
		yearly: 'سنوي',
		transactionsCount: 'إجمالي المعاملات',
		completedGoals: 'الأهداف المكتملة',
		avgDailySpending: 'متوسط الإنفاق اليومي',
		avgTransaction: 'متوسط المعاملة',
		topExpenseCategory: 'أعلى فئة إنفاق',
		lydSymbol: 'د.ل',
		monthlyPerformance: 'الأداء الشهري',
		incomeVsExpense: 'الدخل مقابل المصروف',
		linear: 'خطي',
		noMonthlyData: 'لا توجد بيانات شهرية بعد.',
		spentInsight: 'نظرة على المصروف',
		byCategory: 'حسب الفئة',
		donut: 'دونات',
		noExpenseData: 'لا توجد بيانات مصروفات بعد.',
		withinBudget: 'ضمن الميزانية',
		underMonitoring: 'تحت المراقبة',
		overspent: 'تجاوز/مصروف عالٍ',
		activeGoalsTitle: 'الأهداف النشطة',
		manageGoals: 'إدارة الأهداف',
		noActiveGoals: 'لا توجد أهداف نشطة.',
		target: 'المستهدف:',
		current: 'الحالي:',
		importantAlert: 'تنبيه هام',
		greatAchievement: 'إنجاز رائع',
		smartAdvice: 'رؤية تحليلية',
		useful: 'مفيد',
		addNewTransaction: 'إضافة معاملة جديدة',
		amount: 'المبلغ',
		category: 'الفئة',
		newCategory: 'فئة جديدة',
		quickNewCategory: 'فئة جديدة سريعة',
		categoryName: 'اسم الفئة',
		save: 'حفظ',
		cancel: 'إلغاء',
		date: 'التاريخ',
		optionalNote: 'ملاحظة (اختياري)',
		notePlaceholder: 'تفاصيل إضافية...',
		saveTransaction: 'حفظ المعاملة',
		editTransactionTitle: 'تعديل معاملة',
		updateDataInfo: 'حدث البيانات مع المحافظة على نوع الفئة.',
		updateTransaction: 'تحديث المعاملة',
		addNewGoal: 'إضافة هدف جديد',
		goalSettingInfo: 'حدد هدفاً مالياً واضحاً لتسعى لتحقيقه.',
		goalName: 'اسم الهدف',
		targetAmount: 'المبلغ المستهدف',
		currentAmount: 'المبلغ الحالي',
		deadline: 'الموعد النهائي',
		status: 'الحالة',
		statusPlaceholder: 'مثال: جارٍ التقدم',
		goalNamePlaceholder: 'مثلاً: شراء سيارة',
		achievement: 'إنجاز',
		saveGoal: 'حفظ الهدف',
		planWisely: 'خطط لمصاريفك بحكمة لكل فئة.',
		selectCategory: 'اختر الفئة',
		budgetLimit: 'حد الميزانية',
		periodStart: 'بداية الفترة',
		periodEnd: 'نهاية الفترة',
		saveBudget: 'حفظ الميزانية',
		centralManagement: 'إدارة المنظومة المركزية',
		commandCenter: 'مركز القيادة',
		manageUsersTitle: 'إدارة المستخدمين',
		categoryStructure: 'هيكلة الفئات',
		activeUsers: 'المستخدمين النشطين',
		totalCategories: 'إجمالي الفئات',
		transactionVolume: 'حجم العمليات',
		savingsGoals: 'أهداف الادخار',
		recentlyRegistered: 'المسجلين حديثاً',
		latestMembersInfo: 'قائمة بأحدث الأعضاء المنضمين للمنصة.',
		userRegistry: 'سجل المستخدمين',
		activeMember: 'عضو نشط',
		noNewUsers: 'لا مستخدمين جدد لهذا اليوم.',
		securityTools: 'أدوات الأمان',
		loginTraffic: 'سجلات الدخول',
		securityDescription: 'مراقبة محاولات الدخول وحماية الحسابات من الاختراقات.',
		systemStatusHigh: 'حالة النظام العالية',
		encryptionInfo: 'يتم الآن تشفير كافة التفاعلات المالية عبر بروتوكول TLS 1.3 المتقدم.',
		'طعام': 'طعام',
		'تسوق': 'تسوق',
		'فواتير': 'فواتير',
		'ترفيه': 'ترفيه',
		'هاتف': 'هاتف',
		'رياضة': 'رياضة',
		'تجميل': 'تجميل',
		'تعليم': 'تعليم',
		'اجتماعي': 'اجتماعي',
		'راتب': 'راتب',
		'مكافأة': 'مكافأة',
		'استثمار': 'استثمار',
		'تحويل': 'تحويل',
		'صحة': 'صحة',
		'مواصلات': 'مواصلات',
		'هدايا': 'هدايا',
		'Savings': 'ادخار',
		appBrand: 'قيراط',
		toggleTheme: 'تبديل الوضع',
		secureFinManagement: 'إدارة مالية آمنة • خوارزميات متقدمة',
		landingSummary: 'تتبّع معاملاتك بدقة متناهية، حدّد أهداف الادخار، واطّلع على تحليلات متقدمة بتصميم يجسد الفخامة والوضوح.',
		smartWallet: 'المحفظة الاحترافية',
		overview: 'نظرة عامة',
		currentBalance: 'إجمالي الرصيد',
		growthRate: 'معدل النمو',
		homeGoal: 'هدف شراء منزل',
		deleteConfirm: 'هل أنت متأكد؟ هذا الفعل لا يمكن الرجوع عنه.',
		useful: 'مفيد',
		feedbackSaved: 'تم تسجيل التقييم 👍',
		feedbackError: 'تعذر تسجيل التقييم',
		statusGoalSaved: 'تم حفظ الهدف بنجاح',
		statusGoalUpdated: 'تم تحديث الهدف بنجاح',
		statusGoalDeleted: 'تم حذف الهدف',
		statusBudgetSaved: 'تم إنشاء الميزانية بنجاح',
		statusBudgetUpdated: 'تم تحديث الميزانية بنجاح',
		statusBudgetDeleted: 'تم حذف الميزانية',
		statusTransactionSaved: 'تم حفظ المعاملة بنجاح',
		statusTransactionUpdated: 'تم تحديث المعاملة بنجاح',
		statusTransactionDeleted: 'تم حذف المعاملة',
		statusProfileUpdated: 'تم تحديث الملف الشخصي بنجاح',
		statusUserActivated: 'تم تفعيل المستخدم',
		statusUserDisabled: 'تم تعطيل المستخدم',
		statusAdminPromoted: 'تم منح صلاحية الإدارة',
		statusAdminStripped: 'تم إزالة صلاحية الإدارة',
		statusResetSent: 'تم إرسال رابط إعادة التعيين',
		fastPerformance: 'أداء فائق السرعة',
		fastPerformanceDesc: 'عمليات فورية وإحصاءات لحظية تظهر بمجرد إضافة المعاملة، دون أي انتظار.',
		totalPrivacy: 'خصوصية مطلقة',
		privacyDesc: 'بياناتك مشفرة ومحمية بأعلى المعايير الأمنية، لأن أمانك المالي أولويتنا.',
		aiIntelligence: 'تحليلات متقدمة',
		aiIntelligenceDesc: 'نظام توصيات احترافي يحلل سلوكك المالي ويقدم نصائح مخصصة لزيادة ادخارك.',
		readyToElevate: 'جاهز للارتقاء بحياتك المالية؟',
		ctaSub: 'ابدأ رحلتك اليوم نحو الاستقرار والرفاهية مع قيراط المالي.',
		getStartedFree: 'ابدأ الآن مجاناً',
		copyright: '© {year} قيراط المالي. جميع الحقوق محفوظة.',
		aboutUs: 'عن الشركة',
		privacy: 'الخصوصية',
		terms: 'الشروط',
		transport: 'مواصلات',
		gifts: 'هدايا',
		edit: 'تعديل',
		delete: 'حذف',
		noDeadline: 'بدون موعد',
		userAuthEngine: 'محرك التراخيص والوصول',
		subscriberDatabase: 'قاعدة بيانات المشتركين',
		managePermissionsInfo: 'إدارة صلاحيات الوصول والحسابات النشطة في النظام.',
		accountData: 'بيانات الحساب',
		roleAndRank: 'الرتبة والدور',
		operationStatus: 'الحالة التشغيلية',
		joinDate: 'تاريخ الانضمام',
		sovereignOps: 'العمليات السيادية',
		systemAdmin: 'مدير النظام',
		clientUser: 'مستخدم عميل',
		activeStatus: 'مفعل',
		disabledStatus: 'معطل',
		blockAccess: 'حظر الوصول',
		grantAccess: 'إطلاق الوصول',
		stripAdmin: 'تجريد الإدارة',
		promoteAdmin: 'ترقية لإداري',
		resetPin: 'تصفير الرقم',
		usersCount: 'مستخدم',
		categoryEngine: 'محرك التصنيف والتبويب',
		manageCategoriesTitle: 'إدارة الفئات',
		categoryStructureHeader: 'هيكلة التصنيفات المالية',
		organizeCategoriesInfo: 'عرض وتنظيم الفئات المستخدمة في تبويب الدخل والمصروفات.',
		identificationTitle: 'العنوان التعريفي',
		accountingType: 'النوع المحاسبي',
		visualIcon: 'الرمز البصري',
		financialIncome: 'دخل مالي',
		currentExpense: 'مصروف جاري',
		user: 'المستخدم',
		email: 'البريد',
		result: 'النتيجة',
		ip: 'IP',
		browser: 'المتصفح',
		time: 'الوقت',
		success: 'ناجحة',
		failed: 'فاشلة',
		unknown: 'غير معروف',
		manageTransactionsTitle: 'إدارة المعاملات',
		manageGoalsTitle: 'إدارة الأهداف',
		manageBudgetsTitle: 'إدارة الميزانيات',
		pageDisabledInfo: 'هذه الصفحة معطلة حالياً في لوحة الإدارة.',
		backToAdminHome: 'العودة للرئيسية',
		categoryChartLabel: 'توزيع المصروفات حسب الفئة',
		chartIncome: 'الدخل',
		chartExpense: 'المصروف',
		hide: 'إخفاء',
		show: 'إظهار',
		langSetAr: 'تم ضبط اللغة إلى العربية',
		langSetEn: 'Language set to English',
		lightModeSet: 'تم ضبط الوضع الفاتح',
		darkModeSet: 'تم ضبط الوضع الداكن',
		currencyChanged: 'تم تغيير العملة',
		privacyUpdated: 'تم تحديث الخصوصية',
		congrats: 'مبروك!',
		financialAdvisor: 'مستشارك المالي',
		justNow: 'الآن',
		congratsMsg: '🎊 تهانينا! لقد حققت هدفك بنجاح! 🎊',
		reachedGoal: 'أحسنت! وصلت إلى {amount} د.ل. أنت مثال رائع في الالتزام والادخار! 🌟',
		pastDeadline: '⏰ انتهى موعد الهدف! لكن لا تقلق، يمكنك تمديد الموعد والاستمرار. تبقى {amount} د.ل.',
		urgentAlert: '⚡ تنبيه: باقي {days} يوم فقط! تحتاج لادخار {amount} د.ل يومياً لتحقيق هدفك.',
		greatProgress: '🎉 رائع! تبقى {percent}% فقط وباقي {days} يوم. ادخر {amount} د.ل يومياً لتنجح!',
		almostThere: '🎉 رائع! تبقى {percent}% فقط. أنت قريب جداً من النجاح!',
		keepGoing: '💪 أحسنت! وصلت إلى {percent}% من هدفك. استمر بهذا النهج الرائع!',
		keepGoingDaily: '💪 أحسنت! وصلت إلى {percent}%. ادخر {amount} يومياً لتحقيق هدفك في الموعد.',
		halfWay: '🚀 أنت في منتصف الطريق! تبقى {amount} د.ل. استمر في الادخار بانتظام!',
		halfWayDaily: '🚀 أنت في منتصف الطريق! تبقى {amount} د.ل. خطط لادخار {amount2} يومياً.',
		goodStart: '💡 بداية جيدة! وصلت إلى {percent}%. ادخر {amount} يومياً وستصل بسهولة!',
		goodStartIncome: '💡 بداية جيدة! وصلت إلى {percent}%. قد تحتاج لتعديل الموعد أو زيادة الدخل.',
		goodStartSmall: '💡 بداية جيدة! وصلت إلى {percent}%. حاول ادخار مبلغ بسيط شهرياً.',
		firstStep: '🌟 خطوة أولى ممتازة! كل رحلة تبدأ بخطوة. استمر!',
		firstStepDaily: '🌟 خطوة أولى ممتازة! ادخر {amount} يوميا لتحقيق هدفك.',
		startSaving: '💡 ابدأ الادخار الآن لتحقيق هدفك!',
		greatIncome: '🎉 رائع! دخل ممتاز بقيمة {amount} د.ل. حاول ادخار جزء منه!',
		wellDoneIncome: '🚀 أحسنت! كل دخل يقربك من أهدافك. استمر في العمل الجيد!',
		largeAmountWarning: '⚠️ مبلغ كبير ({amount} د.ل). تأكد من أنه ضروري وضمن ميزانيتك.',
		moderateExpense: '💬 مصروف معتدل. تذكر أن المصاريف الصغيرة تتراكم بمرور الوقت!',
		smallExpense: '✅ مصروف بسيط. أنت تدير أموالك بحكمة!',
		deleteConfirm: 'هل أنت متأكد؟ هذا الفعل لا يمكن الرجوع عنه.',
		thanks: 'شكراً',
		lastTransactions: 'أحدث المعاملات',
		accountSecurity: 'الأمان والخصوصية',
		walletVisibility: 'رؤية المحفظة',
		public: 'عام (مرئي للمدراء)',
		private: 'خاص (مخفي تماماً)',
		systemEfficiency: 'النظام يعمل بكفاءة قصوى • قيراط',
		financialTimeline: 'المسار الزمني المالي',
		monthlyComparison: 'مقارنة شهرية بين تدفقات الدخل وحجم المصروفات.',
		noTimelineData: 'لم يتم العثور على سجلات زمنية كافية.',
		overspendAlerts: 'تنبيهات تجاوز الميزانية',
		overspendInfo: 'تم التجاوز في الفئات التالية:',
		budgetAnalysis: 'تحليل حدود الميزانية',
		plannedVsActual: 'مقارنة دقيقة بين تقديراتك المخططة والواقع المالي.',
		noBudgetsPeriod: 'لا ميزانيات مسجلة لهذه الفترة.',
		statisticalCategory: 'الفئة الإحصائية',
		timePeriod: 'الفترة الزمنية',
		definedCap: 'السقف المحدد',
		actualSpending: 'الإنفاق الفعلي',
		consumptionIndicator: 'مؤشر الاستهلاك',
		from: 'من',
		to: 'إلى',
		categoryBreakdown: 'التفصيل الفئوي',
		noCategoryActivity: 'لا نشاط مسجل للفئات.',
		savingsProgressCenter: 'مركز التقدّم للادخار',
		manageStrategicGoals: 'إدارة الأهداف الاستراتيجية',
		startSettingGoals: 'ابدأ بتحديد أهدافك المالية لنقوم بتتبعها هنا.',
		noTimeline: 'بدون جدول زمني',
		active: 'نشط',
		current: 'الحالي',
		remaining: 'المتبقي',
		adjustGoal: 'ضبط الهدف',
		finalDelete: 'حذف نهائي',
		quickAccessTools: 'الوصول السريع للأدوات',
		adminDashboard: 'لوحة المدير',
		adminDashboardDesc: 'إدارة النظام والأنشطة.',
		manageUsersDesc: 'تفعيل الصلاحيات والحسابات.',
		securityLogs: 'سجلات الأمان',
		securityLogsDesc: 'مراقبة محاولات الدخول.',
		reportsDesc: 'تحليل مالي مفصل للإنفاق.',
		goalsDesc: 'متابعة تقدم ادخارك المخطط.',
		settingsDesc: 'تعديل العملة وواجهة النظام.',
		centralManagement: 'إدارة المنظومة المركزية',
		commandCenter: 'مركز القيادة',
		manageUsersTitle: 'إدارة المستخدمين',
		categoryStructure: 'هيكلة الفئات',
		activeUsers: 'المستخدمين النشطين',
		totalCategories: 'إجمالي الفئات',
		transactionVolume: 'حجم العمليات',
		savingsGoals: 'أهداف الادخار',
		recentlyRegistered: 'المسجلين حديثاً',
		latestMembersInfo: 'قائمة بأحدث الأعضاء المنضمين للمنصة.',
		userRegistry: 'سجل المستخدمين',
		activeMember: 'عضو نشط',
		noNewUsers: 'لا مستخدمين جدد لهذا اليوم.',
		securityTools: 'أدوات الأمان',
		loginLogs: 'سجلات الدخول',
		loginLogsDesc: 'مراقبة محاولات الدخول وحماية الحسابات من الاختراقات.',
		highSystemStatus: 'حالة النظام العالية',
		tlsEncryptionInfo: 'يتم الآن تشفير كافة التفاعلات المالية عبر بروتوكول TLS 1.3 المتقدم.',
		userAuthEngine: 'محرك التراخيص والوصول',
		subscriberDatabase: 'قاعدة بيانات المشتركين',
		usersCount: 'مستخدم',
		systemAdmin: 'مدير النظام',
		clientUser: 'مستخدم عميل',
		activeStatus: 'مفعل',
		disabledStatus: 'معطل',
		blockAccess: 'حظر الوصول',
		grantAccess: 'إطلاق الوصول',
		stripAdmin: 'تجريد الإدارة',
		promoteAdmin: 'ترقية لإداري',
		resetPin: 'تصفير الرقم',
		categoryEngine: 'محرك التصنيف والتبويب',
		categoryStructureHeader: 'هيكلة التصنيفات المالية',
		organizeCategoriesInfo: 'عرض وتنظيم الفئات المستخدمة في تبويب الدخل والمصروفات.',
		identificationTitle: 'العنوان التعريفي',
		accountingType: 'النوع المحاسبي',
		visualIcon: 'الرمز البصري',
		financialIncome: 'دخل مالي',
		currentExpense: 'مصروف جاري',
		manageBudgetsTitle: 'إدارة الميزانيات',
		manageGoalsTitle: 'إدارة الأهداف',
		manageTransactionsTitle: 'إدارة المعاملات',
		pageDisabledInfo: 'تم إيقاف هذه الصفحة في لوحة الإدارة حالياً.',
		backToAdminHome: 'عودة للرئيسية',
		goalSuccessTitle: '🎊 تهانينا! لقد حققت هدفك بنجاح! 🎊',
		goalSuccessDesc: 'أحسنت! وصلت إلى <strong class="text-green-700 dark:text-green-300">{amount} د.ل</strong>. أنت مثال رائع في الالتزام والادخار! 🌟',
		goalDeadlinePast: '⏰ انتهى موعد الهدف! لكن لا تقلق، يمكنك تمديد الموعد والاستمرار. تبقى <strong class="text-purple-700 dark:text-purple-300">{amount} د.ل</strong>.',
		goalUrgentWarning: '⚡ تنبيه: باقي <strong class="text-red-600 dark:text-red-400">{days} يوم</strong> فقط! تحتاج لادخار <strong class="text-purple-700 dark:text-purple-300">{amount} د.ل يومياً</strong> لتحقيق هدفك.',
		goalNearCompletion: '🎉 رائع! تبقى <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong> فقط',
		daysLeftInfo: 'وباقي {days} يوم. ادخر <strong>{amount} د.ل يومياً</strong> لتنجح!',
		almostDone: '. أنت قريب جداً من النجاح!',
		goalProgress75: '💪 أحسنت! وصلت إلى <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong>',
		dailySavingNeeded: '. ادخر <strong>{amount} د.ل يومياً</strong> لتحقيق هدفك في الموعد.',
		keepGoing75: 'من هدفك. استمر على هذا النهج الرائع!',
		goalProgressPath50: '🚀 أنت في منتصف الطريق! تبقى <strong class="text-purple-700 dark:text-purple-300">{amount} د.ل</strong>',
		planDailySaving: '. خطط لادخار <strong>{amount} د.ل يومياً</strong>.',
		saveRegularly: '. استمر في الادخار بانتظام!',
		goodStart: '💡 بداية جيدة! وصلت إلى <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong>',
		dailySavingPossible: '. ادخر <strong>{amount} د.ل يومياً</strong> وستصل بسهولة!',
		adjustDeadlineInfo: '. قد تحتاج لتعديل الموعد أو زيادة الدخل.',
		saveSmallMonthly: '. حاول ادخار مبلغ صغير شهرياً.',
		firstStepExcellent: '🌟 خطوة أولى ممتازة!',
		dailySavingGoal: 'ادخر <strong class="text-purple-700 dark:text-purple-300">{amount} د.ل يومياً</strong> لتحقيق هدفك.',
		keepStepping: 'كل رحلة تبدأ بخطوة. استمر!',
		startSavingNow: '💡 ابدأ الآن بالادخار لتحقيق هدفك!',
		thanks: 'شكراً',
		food: 'طعام',
		shopping: 'تسوق',
		bills: 'فواتير',
		entertainment: 'ترفيه',
		phone: 'هاتف',
		sports: 'رياضة',
		beauty: 'تجميل',
		education: 'تعليم',
		social: 'اجتماعي',
		salary: 'راتب',
		bonus: 'مكافأة',
		investment: 'استثمار',
		transfer: 'تحويل',
		health: 'صحة',
		transport: 'مواصلات',
		gifts: 'هدايا',
		uncategorized: 'غير مصنف',
		failed: 'فاشلة',
		unknown: 'غير معروف',
		manageTransactionsTitle: 'إدارة المعاملات',
		manageGoalsTitle: 'إدارة الأهداف',
		manageBudgetsTitle: 'إدارة الميزانيات',
		pageDisabledInfo: 'تم إيقاف هذه الصفحة في لوحة الإدارة حالياً.',
		backToAdminHome: 'عودة للرئيسية',
		linear: 'خطي',
		donut: 'دونات',
		categoryChartLabel: 'المصروفات حسب الفئة',
		chartIncome: 'الدخل',
		chartExpense: 'المصروف',
		hide: 'إخفاء',
		show: 'إظهار',
		langSetAr: 'تم ضبط اللغة إلى العربية',
		langSetEn: 'Language set to English',
		lightModeSet: 'تم ضبط الوضع الفاتح',
		darkModeSet: 'تم ضبط الوضع الداكن',
		currencyChanged: 'تم تغيير العملة',
		privacyUpdated: 'تم تحديث الخصوصية',
		importantAlert: 'تنبيه هام',
		greatAchievement: 'إنجاز رائع',
		smartAdvice: 'رؤية تحليلية',
		useful: 'مفيد',
		congrats: 'مبروك!',
		financialAdvisor: 'مستشارك المالي',
		justNow: 'الآن',
		analyzing: 'جاري التحليل...',
		spender: 'مبذر',
		wise: 'حكيم',
		balanced: 'متزن',
		frugal: 'مقتصد',
		congratsMsg: '🎊 تهانينا! لقد حققت هدفك بنجاح! 🎊',
		reachedGoal: 'أحسنت! وصلت إلى {amount} د.ل. أنت مثال رائع في الالتزام والادخار! 🌟',
		pastDeadline: '⏰ انتهى موعد الهدف! لكن لا تقلق، يمكنك تمديد الموعد والاستمرار. تبقى {amount} د.ل.',
		urgentAlert: '⚡ تنبيه: باقي {days} يوم فقط! تحتاج لادخار {amount} د.ل يومياً لتحقيق هدفك.',
		greatProgress: '🎉 رائع! تبقى {percent}% فقط وباقي {days} يوم. ادخر {amount} د.ل يومياً لتنجح!',
		almostThere: '🎉 رائع! تبقى {percent}% فقط. أنت قريب جداً من النجاح!',
		keepGoing: '💪 أحسنت! وصلت إلى {percent}% من هدفك. استمر على هذا النهج الرائع!',
		keepGoingDaily: '💪 أحسنت! وصلت إلى {percent}%. ادخر {amount} د.ل يومياً لتحقيق هدفك في الموعد.',
		halfWay: '🚀 أنت في منتصف الطريق! تبقى {amount} د.ل. استمر في الادخار بانتظام!',
		halfWayDaily: '🚀 أنت في منتصف الطريق! تبقى {amount} د.ل. خطط لادخار {amount2} د.ل يومياً.',
		goodStart: '💡 بداية جيدة! وصلت إلى {percent}%. ادخر {amount} د.ل يومياً وستصل بسهولة!',
		goodStartIncome: '💡 بداية جيدة! وصلت إلى {percent}%. قد تحتاج لتعديل الموعد أو زيادة الدخل.',
		goodStartSmall: '💡 بداية جيدة! وصلت إلى {percent}%. حاول ادخار مبلغ صغير شهرياً.',
		firstStep: '🌟 خطوة أولى ممتازة! كل رحلة تبدأ بخطوة. استمر!',
		firstStepDaily: '🌟 خطوة أولى ممتازة! ادخر {amount} د.ل يومياً لتحقيق هدفك.',
		startSaving: '💡 ابدأ الآن بالادخار لتحقيق هدفك!',
		greatIncome: '🎉 رائع! دخل ممتاز بقيمة {amount} د.ل. حاول ادخار جزء منه!',
		wellDoneIncome: '🚀 أحسنت! كل دخل يقربك من أهدافك. استمر في العمل الجيد!',
		largeAmountWarning: '⚠️ مبلغ كبير ({amount} د.ل). تأكد من أنه ضروري وضمن ميزانيتك.',
		moderateExpense: '💬 مصروف معتدل. تذكر أن المصاريف الصغيرة تتراكم بمرور الوقت!',
		smallExpense: '✅ مصروف بسيط. أنت تدير أموالك بحكمة!',
		deleteConfirm: 'هل أنت متأكد؟ هذا الفعل لا يمكن الرجوع عنه.',
		thanks: 'شكراً',
		lastTransactions: 'آخر المعاملات',
		budgetLimit: 'حد الميزانية',
		lydSymbol: 'د.ل',
		periodStart: 'بداية الفترة',
		periodEnd: 'نهاية الفترة',
		saveBudget: 'حفظ الميزانية',
		updateBudget: 'تحديث الميزانية',
		addNewBudget: 'ميزانية جديدة',
		editBudgetTitle: 'تعديل الميزانية',
		planWisely: 'خطط لمصاريفك بحكمة لكل فئة.',
		selectCategory: 'اختر الفئة',
		addNewGoal: 'إضافة هدف جديد',
		addNewTransaction: 'إضافة معاملة جديدة',
		newCategory: 'فئة جديدة',
		quickNewCategory: 'فئة جديدة سريعة',
		save: 'حفظ',
		cancel: 'إلغاء',
		transactionHistory: 'سجل المعاملات',
		newTransaction: 'معاملة جديدة',
		clear: 'مسح',
		all: 'الكل',
		noTransactionsYet: 'لا توجد معاملات بعد',
		startAddingTransactions: 'ابدأ بإضافة الفئات الأساسية ثم سجّل أول عملية دخل أو مصروف.',
		addTransaction: 'إضافة معاملة',
		viewBudgets: 'عرض الميزانيات',
		editTransactionTitle: 'تعديل معاملة',
		updateDataInfo: 'حدث البيانات مع المحافظة على نوع الفئة.',
		amount: 'المبلغ',
		date: 'التاريخ',
		category: 'الفئة',
		categoryName: 'اسم الفئة',
		optionalNote: 'ملاحظة',
		notePlaceholder: 'تفاصيل إضافية (اختياري)',
		updateTransaction: 'تحديث المعاملة',
		activeStatus: 'نشط',
		completedStatus: 'مكتمل',
		currentAvatar: 'الصورة الحالية',
		appLogo: 'شعار قيراط',
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
		reports: 'Reports',
		stats: 'Statistics',
		profile: 'Profile',
		settings: 'Settings',
		logout: 'Logout',
		login: 'Login',
		register: 'Register',
		welcome: 'Welcome',
		premiumVersion: 'Premium Version',
		dashboardTagline: 'Your financial dashboard, built with modern design and smart tools.',
		transaction: 'Transaction',
		goal: 'Goal',
		currentBalance: 'Current Balance',
		income: 'Income',
		expense: 'Expense',
		analyzing: 'Analyzing...',
		basedOnSpending: 'Based on your recent spending',
		expenseDistribution: 'Expense Distribution',
		topExpenseAnalysis: 'Analysis of top spending categories',
		noData: 'No data',
		financialGoals: 'Financial Goals',
		viewAll: 'View All',
		secureFinance: 'Secure Finance • Smart Algorithms',
		controlPower: 'Power of Control',
		onFuture: 'Over Your Financial Future',
		landingDescription: 'Track your transactions precisely, set savings goals, and get smart insights with a design that embodies luxury and clarity.',
		fullyCompatible: 'Fully Compatible',
		premiumInterfaces: 'Premium Interfaces',
		proReports: 'Professional Reports',
		smartWallet: 'Smart Wallet',
		overview: 'Overview',
		growthRate: 'Growth Rate',
		homeGoal: 'Home Purchase Goal',
		welcomeBack: 'Welcome back to Qiratae Finance',
		email: 'Email',
		password: 'Password',
		rememberMe: 'Remember Me',
		forgotPassword: 'Forgot Password?',
		enter: 'Enter',
		noAccount: 'No account?',
		backToHome: 'Back to Home',
		notifications: 'Notifications',
		markAllRead: 'Mark all as read',
		noNotifications: 'No new notifications',
		viewAllNotifications: 'View All Notifications',
		myAccount: 'My Account',
		adminPanel: 'Admin Panel',
		adminDashboard: 'Admin Dashboard',
		users: 'Users',
		loginLogs: 'Login Logs',
		registerJoin: 'Register your details to join and manage your money in Qiratae Finance',
		name: 'Name',
		fullName: 'Full Name',
		confirmPassword: 'Confirm Password',
		signUp: 'Sign Up',
		haveAccount: 'Already have an account?',
		back: 'Back',
		activeGoals: 'Active Goals',
		newGoal: 'New Goal',
		noGoalsYet: 'No goals yet',
		startAddingGoal: 'Start by adding a savings or purchase goal and your progress will appear elegantly here.',
		createGoal: 'Create Goal',
		edit: 'Edit',
		delete: 'Delete',
		collected: 'Collected:',
		left: 'Left:',
		transactionHistory: 'Transaction History',
		newTransaction: 'New Transaction',
		searchPlaceholder: 'Search description or category',
		clear: 'Clear',
		all: 'All',
		noTransactionsYet: 'No transactions yet',
		startAddingTransactions: 'Start by adding basic categories, then record your first income or expense.',
		addTransaction: 'Add Transaction',
		viewBudgets: 'View Budgets',
		businessIntelligence: 'Financial Business Intelligence',
		analyticalReports: 'Analytical Reports',
		exportData: 'Export Data',
		txtFormat: 'Text Format',
		excelFormat: 'Excel (Professional)',
		pdfFormat: 'PDF (Print Ready)',
		financialPlanning: 'Financial Planning',
		searchTransactionsPlaceholder: 'Search transactions or categories...',
		currentMonth: 'Current Month',
		'30days': '30 Days',
		quarterly: 'Quarterly',
		currentYear: 'Current Year',
		totalIncomeLabel: 'Total Income',
		totalExpenseLabel: 'Total Expense',
		netBalance: 'Net Balance',
		savingsRate: 'Savings Rate',
		analyticalAnalysis: 'Relative spending analysis by main categories.',
		noDataPeriod: 'No spending data for the selected period.',
		budgets: 'Budgets',
		newBudget: 'New Budget',
		noBudgetsYet: 'No budgets yet',
		addCategoryLimit: 'Add a category and set a monthly limit to monitor spending and stay on plan.',
		createBudget: 'Create Budget',
		spent: 'Spent',
		active: 'Active',
		completed: 'Completed',
		accountSettings: 'Account Settings',
		updatePersonalInfo: 'Update your personal information and profile picture.',
		adminLabel: 'System Admin',
		fullNameLabel: 'Full Name',
		displayNamePlaceholder: 'Display Name',
		emailDisplayOnly: 'Email (View Only)',
		emailChangeInfo: 'Email is changed from advanced security settings.',
		saveChanges: 'Save Changes',
		advancedSettings: 'Advanced Settings',
		quickAccessTools: 'Quick Access Tools',
		settingsPanel: 'Settings Panel',
		fullSystemControl: 'Full control over system appearance and advanced options.',
		preferredLanguage: 'Preferred Language',
		uiInterface: 'User Interface',
		lightMode: 'Light Mode',
		darkMode: 'Dark Mode',
		defaultCurrency: 'Default Currency',
		strategicNavigation: 'Strategic Navigation',
		manageUsers: 'Manage Users',
		lyd: 'Libyan Dinar (LYD)',
		usd: 'US Dollar ($)',
		eur: 'Euro (€)',
		details: 'View Details',
		noNotificationsPage: 'No Notifications',
		latestActivityInfo: 'We will inform you of the latest activity here.',
		notification: 'Notification',
		reportsHeader: 'Reports & Analytics Dashboard',
		reportsTitle: 'Reports',
		instantSearch: 'Instant search or type to analyze',
		monthly: 'Monthly',
		weekly: 'Weekly',
		daily: 'Daily',
		yearly: 'Yearly',
		transactionsCount: 'Total Transactions',
		completedGoals: 'Completed Goals',
		avgDailySpending: 'Average Daily Spending',
		avgTransaction: 'Average Transaction',
		topExpenseCategory: 'Top Expense Category',
		lydSymbol: 'LYD',
		monthlyPerformance: 'Monthly Performance',
		incomeVsExpense: 'Income vs Expense',
		linear: 'Linear',
		noMonthlyData: 'No monthly data yet.',
		spentInsight: 'Spending Insight',
		byCategory: 'By Category',
		donut: 'Donut',
		noExpenseData: 'No expense data yet.',
		withinBudget: 'Within Budget',
		underMonitoring: 'Under Monitoring',
		overspent: 'Overspent/High Spending',
		activeGoalsTitle: 'Active Goals',
		manageGoals: 'Manage Goals',
		noActiveGoals: 'No active goals.',
		target: 'Target:',
		current: 'Current:',
		importantAlert: 'Important Alert',
		greatAchievement: 'Great Achievement',
		smartAdvice: 'Smart Advice',
		useful: 'Useful',
		addNewTransaction: 'Add New Transaction',
		amount: 'Amount',
		category: 'Category',
		newCategory: 'New Category',
		quickNewCategory: 'Quick New Category',
		categoryName: 'Category Name',
		save: 'Save',
		cancel: 'Cancel',
		date: 'Date',
		optionalNote: 'Note (Optional)',
		notePlaceholder: 'Additional details...',
		saveTransaction: 'Save Transaction',
		editTransactionTitle: 'Edit Transaction',
		updateDataInfo: 'Update data while maintaining category type.',
		updateTransaction: 'Update Transaction',
		addNewGoal: 'Add New Goal',
		goalSettingInfo: 'Set a clear financial goal to strive for.',
		goalName: 'Goal Name',
		targetAmount: 'Target Amount',
		currentAmount: 'Current Amount',
		deadline: 'Deadline',
		status: 'Status',
		statusPlaceholder: 'Example: In Progress',
		goalNamePlaceholder: 'Example: Buying a car',
		achievement: 'Achievement',
		saveGoal: 'Save Goal',
		planWisely: 'Plan your expenses wisely for each category.',
		selectCategory: 'Select Category',
		budgetLimit: 'Budget Limit',
		periodStart: 'Period Start',
		periodEnd: 'Period End',
		saveBudget: 'Save Budget',
		centralManagement: 'Central System Management',
		commandCenter: 'Command Center',
		manageUsersTitle: 'Manage Users',
		categoryStructure: 'Category Structure',
		activeUsers: 'Active Users',
		totalCategories: 'Total Categories',
		transactionVolume: 'Transaction Volume',
		savingsGoals: 'Savings Goals',
		recentlyRegistered: 'Recently Registered',
		latestMembersInfo: 'List of the newest members to join the platform.',
		userRegistry: 'User Registry',
		activeMember: 'Active Member',
		noNewUsers: 'No new users for today.',
		securityTools: 'Security Tools',
		loginTraffic: 'Login Logs',
		securityDescription: 'Control your data visibility and AI privacy policy.',
		aiTrainingNotice: 'When public mode is active, your data (anonymized) will be used to train models for better insights.',
		systemStatusHigh: 'High System Status',
		encryptionInfo: 'All financial interactions are now encrypted via advanced TLS 1.3 protocol.',
		'طعام': 'Food',
		'تسوق': 'Shopping',
		'فواتير': 'Bills',
		'ترفيه': 'Entertainment',
		'هاتف': 'Phone',
		'رياضة': 'Sports',
		'تجميل': 'Beauty',
		'تعليم': 'Education',
		'اجتماعي': 'Social',
		'راتب': 'Salary',
		'مكافأة': 'Bonus',
		'استثمار': 'Investment',
		'تحويل': 'Transfer',
		'صحة': 'Health',
		'مواصلات': 'Transport',
		'هدايا': 'Gifts',
		'Savings': 'Savings',
		edit: 'Edit',
		delete: 'Delete',
		noDeadline: 'No Deadline',
		userAuthEngine: 'Auth & Access Engine',
		subscriberDatabase: 'Subscriber Database',
		managePermissionsInfo: 'Manage access permissions and active accounts in the system.',
		accountData: 'Account Data',
		roleAndRank: 'Role & Rank',
		operationStatus: 'Operational Status',
		joinDate: 'Join Date',
		sovereignOps: 'Sovereign Operations',
		systemAdmin: 'System Admin',
		clientUser: 'Client User',
		activeStatus: 'Active',
		disabledStatus: 'Disabled',
		blockAccess: 'Block Access',
		grantAccess: 'Grant Access',
		stripAdmin: 'Strip Admin',
		promoteAdmin: 'Promote to Admin',
		resetPin: 'Reset PIN',
		usersCount: 'Users',
		categoryEngine: 'Classification & Tabulation Engine',
		manageCategoriesTitle: 'Manage Categories',
		categoryStructureHeader: 'Financial Classification Structure',
		organizeCategoriesInfo: 'View and organize categories used for income and expense tabulation.',
		identificationTitle: 'Identification Title',
		accountingType: 'Accounting Type',
		visualIcon: 'Visual Icon',
		financialIncome: 'Financial Income',
		currentExpense: 'Current Expense',
		user: 'User',
		email: 'Email',
		result: 'Result',
		ip: 'IP',
		browser: 'Browser',
		time: 'Time',
		success: 'Success',
		failed: 'Failed',
		unknown: 'Unknown',
		manageTransactionsTitle: 'Manage Transactions',
		manageGoalsTitle: 'Manage Goals',
		manageBudgetsTitle: 'Manage Budgets',
		pageDisabledInfo: 'This page is currently disabled in the admin panel.',
		backToAdminHome: 'Back to Home',
		categoryChartLabel: 'Expenses by Category',
		chartIncome: 'Income',
		chartExpense: 'Expense',
		hide: 'Hide',
		show: 'Show',
		langSetAr: 'Language set to Arabic',
		langSetEn: 'Language set to English',
		lightModeSet: 'Light mode set',
		darkModeSet: 'Dark mode set',
		currencyChanged: 'Currency changed',
		privacyUpdated: 'Privacy updated',
		congrats: 'Congrats!',
		financialAdvisor: 'Financial Advisor',
		justNow: 'Just now',
		congratsMsg: '🎊 Congratulations! You have successfully achieved your goal! 🎊',
		reachedGoal: 'Well done! You reached {amount} LYD. You are a great example of commitment and saving! 🌟',
		pastDeadline: '⏰ Goal deadline has ended! But don\'t worry, you can extend the date and continue. {amount} LYD remaining.',
		urgentAlert: '⚡ Alert: Only {days} days left! You need to save {amount} LYD daily to achieve your goal.',
		greatProgress: '🎉 Great! Only {percent}% remaining and {days} days left. Save {amount} LYD daily to succeed!',
		almostThere: '🎉 Great! Only {percent}% remaining. You are very close to success!',
		keepGoing: '💪 Well done! You reached {percent}% of your goal. Keep up this great approach!',
		keepGoingDaily: '💪 Well done! You reached {percent}%. Save {amount} daily to achieve your goal on time.',
		halfWay: '🚀 You are half way there! {amount} LYD remaining. Keep saving regularly!',
		halfWayDaily: '🚀 You are half way there! {amount} LYD remaining. Plan to save {amount2} daily.',
		goodStart: '💡 Good start! You reached {percent}%. Save {amount} daily and you will get there easily!',
		goodStartIncome: '💡 Good start! You reached {percent}%. You might need to adjust the deadline or increase income.',
		goodStartSmall: '💡 Good start! You reached {percent}%. Try to save a small amount monthly.',
		firstStep: '🌟 Excellent first step! Every journey starts with a step. Keep going!',
		firstStepDaily: '🌟 Excellent first step! Save {amount} daily to achieve your goal.',
		startSaving: '💡 Start saving now to achieve your goal!',
		greatIncome: '🎉 Great! Excellent income of {amount} LYD. Try to save some of it!',
		wellDoneIncome: '🚀 Well done! Every income brings you closer to your goals. Keep up the good work!',
		largeAmountWarning: '⚠️ Large amount ({amount} LYD). Make sure it is necessary and within your budget.',
		moderateExpense: '💬 Moderate expense. Remember that small expenses accumulate over time!',
		smallExpense: '✅ Simple expense. You are managing your money wisely!',
		deleteConfirm: 'Are you sure? This action cannot be undone.',
		thanks: 'Thanks',
		lastTransactions: 'Latest Transactions',
		appBrand: 'Qirat',
		toggleTheme: 'Toggle Theme',
		secureFinManagement: 'Secure Financial Management • Advanced Algorithms',
		landingSummary: 'Track your transactions with utmost accuracy, set savings goals, and view advanced statistics with a design that embodies luxury and clarity.',
		fastPerformance: 'Super Fast Performance',
		fastPerformanceDesc: 'Instant operations and real-time statistics appear as soon as the transaction is added, without any waiting.',
		aiIntelligenceDesc: 'An advanced recommendation system that analyzes your financial behavior and provides personalized tips to increase your savings.',
		totalPrivacy: 'Absolute Privacy',
		privacyDesc: 'Your data is encrypted and protected with the highest security standards, because your financial security is our priority.',
		aiIntelligence: 'Artificial Intelligence',
		statusResetSent: 'Reset link sent',
		failed: 'Failed',
		unknown: 'Unknown',
		manageTransactionsTitle: 'Manage Transactions',
		manageGoalsTitle: 'Manage Goals',
		manageBudgetsTitle: 'Manage Budgets',
		pageDisabledInfo: 'This page is currently disabled in the admin panel.',
		backToAdminHome: 'Back to Home',
		categoryChartLabel: 'Expenses by Category',
		chartIncome: 'Income',
		chartExpense: 'Expense',
		hide: 'Hide',
		show: 'Show',
		langSetAr: 'تم ضبط اللغة إلى العربية',
		langSetEn: 'Language set to English',
		lightModeSet: 'Light mode set',
		darkModeSet: 'Dark mode set',
		currencyChanged: 'Currency changed',
		privacyUpdated: 'Privacy updated',
		congrats: 'Congrats!',
		financialAdvisor: 'Financial Advisor',
		justNow: 'Just now',
		analyzing: 'Analyzing...',
		spender: 'Big Spender',
		wise: 'Wise Saver',
		balanced: 'Balanced',
		frugal: 'Frugal',
		congratsMsg: '🎊 Congratulations! You have successfully achieved your goal! 🎊',
		reachedGoal: 'Well done! You reached {amount} LYD. You are a great example of commitment and saving! 🌟',
		pastDeadline: '⏰ Goal deadline has ended! But don\'t worry, you can extend the date and continue. {amount} LYD remaining.',
		urgentAlert: '⚡ Alert: Only {days} days left! You need to save {amount} LYD daily to achieve your goal.',
		greatProgress: '🎉 Great! Only {percent}% remaining and {days} days left. Save {amount} LYD daily to succeed!',
		almostThere: '🎉 Great! Only {percent}% remaining. You are very close to success!',
		keepGoing: '💪 Well done! You reached {percent}% of your goal. Keep up this great approach!',
		keepGoingDaily: '💪 Well done! You reached {percent}%. Save {amount} daily to achieve your goal on time.',
		halfWay: '🚀 You are half way there! {amount} LYD remaining. Keep saving regularly!',
		halfWayDaily: '🚀 You are half way there! {amount} LYD remaining. Plan to save {amount2} daily.',
		childProgress: '💡 Good start! You reached {percent}%. Save {amount} daily and you will get there easily!',
		goodStart: '💡 Good start! You reached {percent}%. Save {amount} daily and you will get there easily!',
		goodStartIncome: '💡 Good start! You reached {percent}%. You might need to adjust the deadline or increase income.',
		goodStartSmall: '💡 Good start! You reached {percent}%. Try to save a small amount monthly.',
		firstStep: '🌟 Excellent first step! Every journey starts with a step. Keep going!',
		firstStepDaily: '🌟 Excellent first step! Save {amount} daily to achieve your goal.',
		startSaving: '💡 Start saving now to achieve your goal!',
		greatIncome: '🎉 Great! Excellent income of {amount} LYD. Try to save some of it!',
		wellDoneIncome: '🚀 Well done! Every income brings you closer to your goals. Keep up the good work!',
		largeAmountWarning: '⚠️ Large amount ({amount} LYD). Make sure it is necessary and within your budget.',
		moderateExpense: '💬 Moderate expense. Remember that small expenses accumulate over time!',
		smallExpense: '✅ Simple expense. You are managing your money wisely!',
		deleteConfirm: 'Are you sure you want to delete this?',
		thanks: 'Thanks',
		lastTransactions: 'Latest Transactions',
		accountSecurity: 'Security & Privacy',
		walletVisibility: 'Wallet Visibility',
		public: 'Public (Visible to Admins)',
		private: 'Private (Completely Hidden)',
		systemEfficiency: 'System is operating at peak efficiency • Qiratae Finance',
		financialTimeline: 'Financial Timeline',
		monthlyComparison: 'Monthly comparison of income streams and expenses volume.',
		noTimelineData: 'No sufficient time records found.',
		overspendAlerts: 'Budget Overspend Alerts',
		overspendInfo: 'Overspend detected in the following categories:',
		budgetAnalysis: 'Budget Limit Analysis',
		plannedVsActual: 'Precise comparison between planned estimates and financial reality.',
		noBudgetsPeriod: 'No budgets recorded for this period.',
		statisticalCategory: 'Statistical Category',
		timePeriod: 'Time Period',
		definedCap: 'Defined Cap',
		actualSpending: 'Actual Spending',
		consumptionIndicator: 'Consumption Indicator',
		from: 'From',
		to: 'To',
		categoryBreakdown: 'Category Breakdown',
		noCategoryActivity: 'No activity recorded for categories.',
		savingsProgressCenter: 'Savings Progress Center',
		manageStrategicGoals: 'Manage Strategic Goals',
		startSettingGoals: 'Start setting your financial goals to track them here.',
		noTimeline: 'No timeline',
		active: 'Active',
		current: 'Current',
		remaining: 'Remaining',
		adjustGoal: 'Adjust Goal',
		finalDelete: 'Final Delete',
		quickAccessTools: 'Quick Access Tools',
		adminDashboard: 'Admin Dashboard',
		adminDashboardDesc: 'System and activity management.',
		manageUsersDesc: 'Activate permissions and accounts.',
		securityLogs: 'Security Logs',
		securityLogsDesc: 'Monitor login attempts.',
		reportsDesc: 'Detailed financial spending analysis.',
		goalsDesc: 'Track your planned savings progress.',
		settingsDesc: 'Change currency and system interface.',
		centralManagement: 'Central Management System',
		commandCenter: 'Command Center',
		manageUsersTitle: 'User Management',
		categoryStructure: 'Category Structure',
		activeUsers: 'Active Users',
		totalCategories: 'Total Categories',
		transactionVolume: 'Transaction Volume',
		savingsGoals: 'Savings Goals',
		recentlyRegistered: 'Recently Registered',
		latestMembersInfo: 'List of the latest members who joined.',
		userRegistry: 'User Registry',
		activeMember: 'Active Member',
		noNewUsers: 'No new users today.',
		securityTools: 'Security Tools',
		loginLogs: 'Login Logs',
		loginLogsDesc: 'Monitor login attempts and protect accounts.',
		highSystemStatus: 'High System Status',
		tlsEncryptionInfo: 'All financial interactions are encrypted via TLS 1.3.',
		userAuthEngine: 'Permissions & Access Engine',
		subscriberDatabase: 'Subscribers Database',
		usersCount: 'User',
		systemAdmin: 'System Admin',
		clientUser: 'Client User',
		activeStatus: 'Active',
		disabledStatus: 'Disabled',
		blockAccess: 'Block Access',
		grantAccess: 'Grant Access',
		stripAdmin: 'Strip Admin',
		promoteAdmin: 'Promote to Admin',
		resetPin: 'Reset PIN',
		categoryEngine: 'Classification Engine',
		categoryStructureHeader: 'Financial Structure',
		organizeCategoriesInfo: 'View and organize categories for income and expenses.',
		identificationTitle: 'Identification Title',
		accountingType: 'Accounting Type',
		visualIcon: 'Visual Icon',
		financialIncome: 'Financial Income',
		currentExpense: 'Current Expense',
		manageBudgetsTitle: 'Manage Budgets',
		manageGoalsTitle: 'Manage Goals',
		manageTransactionsTitle: 'Manage Transactions',
		pageDisabledInfo: 'This page is currently disabled in the admin panel.',
		backToAdminHome: 'Back to Admin Home',
		goalSuccessTitle: '🎊 Congratulations! You achieved your goal! 🎊',
		goalSuccessDesc: 'Well done! You reached <strong class="text-green-700 dark:text-green-300">{amount} LYD</strong>. You are a great example of commitment! 🌟',
		goalDeadlinePast: '⏰ Deadline passed! But do not worry, you can extend the date and continue. <strong class="text-purple-700 dark:text-purple-300">{amount} LYD</strong> remaining.',
		goalUrgentWarning: '⚡ Alert: only <strong class="text-red-600 dark:text-red-400">{days} days</strong> left! You need to save <strong class="text-purple-700 dark:text-purple-300">{amount} LYD daily</strong>.',
		goalNearCompletion: '🎉 Great! Only <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong> left',
		daysLeftInfo: ' and {days} days remaining. Save <strong>{amount} LYD daily</strong> to succeed!',
		almostDone: '. You are very close to success!',
		goalProgress75: '💪 Well done! You reached <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong>',
		dailySavingNeeded: '. Save <strong>{amount} LYD daily</strong> to achieve your goal on time.',
		keepGoing75: ' of your goal. Keep up this great approach!',
		goalProgressPath50: '🚀 You are halfway there! <strong class="text-purple-700 dark:text-purple-300">{amount} LYD</strong> left',
		planDailySaving: '. Plan to save <strong>{amount} LYD daily</strong>.',
		saveRegularly: '. Continue saving regularly!',
		goodStart: '💡 Good start! You reached <strong class="text-purple-700 dark:text-purple-300">{percent}%</strong>',
		dailySavingPossible: '. Save <strong>{amount} LYD daily</strong> and you will get there easily!',
		adjustDeadlineInfo: '. You might need to adjust the deadline or increase income.',
		saveSmallMonthly: '. Try saving a small amount monthly.',
		firstStepExcellent: '🌟 Excellent first step!',
		dailySavingGoal: 'Save <strong class="text-purple-700 dark:text-purple-300">{amount} LYD daily</strong> to achieve your goal.',
		keepStepping: 'Every journey starts with a step. Keep going!',
		startSavingNow: '💡 Start saving now to achieve your goal!',
		thanks: 'Thanks',
		food: 'Food',
		shopping: 'Shopping',
		bills: 'Bills',
		entertainment: 'Entertainment',
		phone: 'Phone',
		sports: 'Sports',
		beauty: 'Beauty',
		education: 'Education',
		social: 'Social',
		salary: 'Salary',
		bonus: 'Bonus',
		investment: 'Investment',
		transfer: 'Transfer',
		health: 'Health',
		transport: 'Transport',
		gifts: 'Gifts',
		uncategorized: 'Uncategorized',
		budgetLimit: 'Budget Limit',
		lydSymbol: 'LYD',
		periodStart: 'Period Start',
		periodEnd: 'Period End',
		saveBudget: 'Save Budget',
		updateBudget: 'Update Budget',
		addNewBudget: 'New Budget',
		editBudgetTitle: 'Edit Budget',
		planWisely: 'Plan your expenses wisely for each category.',
		selectCategory: 'Select Category',
		addNewGoal: 'Add New Goal',
		addNewTransaction: 'Add New Transaction',
		newCategory: 'New Category',
		quickNewCategory: 'Quick New Category',
		save: 'Save',
		cancel: 'Cancel',
		transactionHistory: 'Transaction History',
		newTransaction: 'New Transaction',
		clear: 'Clear',
		all: 'All',
		noTransactionsYet: 'No transactions yet',
		startAddingTransactions: 'Start by adding basic categories, then record your first income or expense.',
		addTransaction: 'Add Transaction',
		viewBudgets: 'View Budgets',
		editTransactionTitle: 'Edit Transaction',
		updateDataInfo: 'Update data while maintaining category type.',
		amount: 'Amount',
		date: 'Date',
		category: 'Category',
		categoryName: 'Category Name',
		optionalNote: 'Note',
		notePlaceholder: 'Additional details (optional)',
		updateTransaction: 'Update Transaction',
		activeStatus: 'Active',
		completedStatus: 'Completed',
		currentAvatar: 'Current Avatar',
		appLogo: 'Qiratae Logo',
		footerText: 'Qiratae - Arabic Financial Management built with Laravel + Bootstrap • v2.4 Platinum',
	},
};

const applyTheme = (theme) => {
	const safeTheme = theme === 'dark' ? 'dark' : 'light';
	htmlEl.dataset.theme = safeTheme;

	// Update inline background to match the theme precisely to avoid transition flickers on initial apply
	htmlEl.style.backgroundColor = safeTheme === 'dark' ? '#020617' : '#FAFAF9';

	const toggle = document.getElementById('themeToggle');
	if (toggle) {
		const label = toggle.querySelector('.btn-label');
		if (label) label.textContent = safeTheme === 'dark' ? i18n[currentLang()].dark : i18n[currentLang()].light;
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
			let text = i18n[safeLang][key];

			// Handle variable interpolation
			if (el.dataset.i18nVars) {
				const vars = JSON.parse(el.dataset.i18nVars);
				Object.keys(vars).forEach((v) => {
					text = text.replace(`{${v}}`, vars[v]);
				});
			}

			if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
				el.placeholder = text;
			} else {
				el.textContent = text;
			}
		}
	});

	document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
		const key = el.dataset['i18nPlaceholder'];
		if (key && i18n[safeLang]?.[key]) {
			el.placeholder = i18n[safeLang][key];
		}
	});

	document.querySelectorAll('[data-i18n-title]').forEach((el) => {
		const key = el.dataset['i18nTitle'];
		if (key && i18n[safeLang]?.[key]) {
			el.title = i18n[safeLang][key];
			if (el.hasAttribute('aria-label')) el.setAttribute('aria-label', i18n[safeLang][key]);
		}
	});

	document.querySelectorAll('[data-i18n-aria-label]').forEach((el) => {
		const key = el.dataset['i18nAriaLabel'];
		if (key && i18n[safeLang]?.[key]) {
			el.setAttribute('aria-label', i18n[safeLang][key]);
		}
	});

	document.querySelectorAll('[data-i18n-dynamic]').forEach((el) => {
		const text = el.textContent.trim();
		if (statusMappings[text]) {
			const key = statusMappings[text];
			if (i18n[safeLang]?.[key]) {
				el.textContent = i18n[safeLang][key];
			}
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
		const parts = hex.replace(/rgba?\(|\)/g, '').split(',').map(p => p.trim());
		const [r, g, b] = parts;
		return `rgba(${r}, ${g}, ${b}, ${alpha})`;
	}
	const h = hex.replace('#', '');
	const r = parseInt(h.substring(0, 2), 16);
	const g = parseInt(h.substring(2, 4), 16);
	const b = parseInt(h.substring(4, 6), 16);
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
	destroyAll() { Object.values(this.instances).forEach(c => c.destroy()); this.instances = {}; },
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
					label: i18n[currentLang()].chartIncome,
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
					label: i18n[currentLang()].chartExpense,
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
	const fallback = ['#c9a227', '#d59b4e', '#b07f2f', '#d35400', '#4e5a3d', '#4a6572', '#e6b450', '#9c6b3b', '#7a6b5a'];
	const colors = (categories || []).map((c, i) => palette[c] || fallback[i % fallback.length]);
	const options = themeChartOptions();
	const chart = new Chart(canvas, {
		type: 'pie',
		data: {
			labels: categories,
			datasets: [
				{
					label: i18n[currentLang()].categoryChartLabel,
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

	const fallback = ['#c9a227', '#d59b4e', '#b07f2f', '#d35400', '#4e5a3d', '#4a6572', '#e6b450', '#9c6b3b', '#7a6b5a'];
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
				label: i18n[currentLang()].chartExpense,
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

// 11. Luxury Micro-interactions
const initializeMicroInteractions = () => {
	// A. Dynamic Background Glow
	const glow = document.createElement('div');
	glow.className = 'cursor-glow';
	document.body.appendChild(glow);

	document.addEventListener('mousemove', (e) => {
		glow.style.left = `${e.clientX}px`;
		glow.style.top = `${e.clientY}px`;
	});
};

document.addEventListener('DOMContentLoaded', () => {
	bootstrapToggles();
	renderBar();
	renderPie();
	renderHomeExpensePie();
	initializeMicroInteractions();

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
			if (label) label.textContent = isHidden ? i18n[currentLang()].hide : i18n[currentLang()].show;
		});
	});
};

document.addEventListener('DOMContentLoaded', attachPasswordToggles);

// Export to global scope for use in Blade onsubmit attributes
window.i18n = i18n;
window.currentLang = currentLang;
