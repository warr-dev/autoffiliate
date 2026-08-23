<script lang="ts">
	import { onMount } from 'svelte';
	import { router } from '@inertiajs/svelte';
	import AppLayout from '@/layouts/AppLayout.svelte';

	let {
		workflows = [],
		socialAccounts = [],
		settings = {},
	} = $props<{
		workflows?: Array<any>;
		socialAccounts?: Array<any>;
		settings?: Record<string, any>;
	}>();

	const API_BASE = '';

	function getCsrfToken(): string {
		if (typeof document === 'undefined') return '';
		return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
	}

	// Simple WebSocket client fallback shim
	const wsClient = {
		isConnected: false,
		subscribe: (callback: (e: any) => void) => () => {},
	};
	type WsTaskProgressEvent = any;

	interface GeneratedPost {
		title: string;
		caption: string;
		image: string;
		postedAt: string;
		targetPage: string;
		postUrl?: string;
	}

	interface ScheduledRule {
		id: string;
		name: string;
		category: 'Connection & Community' | 'Brand Promotion' | 'Affiliate Deals' | string;
		frequency: 'daily' | 'interval' | 'weekly' | string;
		times: string[];
		intervalHours?: number;
		days?: string[];
		targetPage: string;
		workflowActions: string[];
		actionContexts?: Record<string, string>;
		generalContext?: string;
		weatherContext?: string;
		occasionContext?: string;
		tones?: string[];
		personas?: string[];
		customPersona?: string;
		manualPrompt?: string;
		status: 'active' | 'disabled';
		lastRun: string;
		nextRun: string;
		lastGeneratedPost?: GeneratedPost;
	}

	interface EventTrigger {
		id: string;
		name: string;
		eventSource: string;
		condition: string;
		targetPage: string;
		action: string;
		status: 'active' | 'disabled';
		totalFired: number;
		lastGeneratedPost?: GeneratedPost;
	}

	interface AutomationLog {
		id: string;
		timestamp: string;
		type: 'scheduled' | 'event' | 'manual';
		ruleName: string;
		dealTitle: string;
		targetPage: string;
		status: 'SUCCESS' | 'FAILED' | 'RUNNING';
		postUrl?: string;
		caption?: string;
	}

	interface WorkflowActionDefinition {
		id: string;
		label: string;
		title: string;
		docDescription: string;
		inputOutputDoc: string;
		contextPlaceholder: string;
	}

	// Active tab inside Automated screen
	let activeTab = $state<'scheduled' | 'event' | 'logs'>('scheduled');

	// Execution Logs Filter State
	let logSearchQuery = $state('');
	let logTypeFilter = $state<'all' | 'scheduled' | 'event' | 'manual'>('all');
	let logStatusFilter = $state<'all' | 'SUCCESS' | 'FAILED' | 'RUNNING'>('all');
	let logRuleFilter = $state<string | null>(null);

	// Notification toast message & optional post link
	let actionNotification = $state('');
	let actionNotificationType = $state<'success' | 'warning' | 'info'>('info');
	let actionNotificationLink = $state<string | null>(null);

	// Running Rule State for Animated Visual Feedback
	let runningRuleId = $state<string | null>(null);
	let activeStepIndex = $state<number>(-1);
	let runningTriggerId = $state<string | null>(null);

	// Post Preview Modal State
	let selectedPreviewPost = $state<GeneratedPost | null>(null);

	// Modal Step State (1: Template choice, 2: Schedule & Rule setup, 3: Action steps & Summary)
	let wizardStep = $state<1 | 2 | 3>(1);

	// Action Documentation Expanded Card ID
	let expandedDocActionId = $state<string | null>(null);

	// Live Content Preview State Before Saving Rule
	let isTestingWorkflow = $state(false);
	let showTestPreviewModal = $state(false);
	let testPreviewResult = $state<{
		title: string;
		caption: string;
		image: string;
		executedSteps: string[];
	} | null>(null);

	// Global ESC keydown handler to close active modals
	$effect(() => {
		function handleKeydown(e: KeyboardEvent) {
			if (e.key === 'Escape') {
				if (showTestPreviewModal) {
					showTestPreviewModal = false;
				} else if (selectedPreviewPost) {
					selectedPreviewPost = null;
				} else if (showNewScheduleModal) {
					showNewScheduleModal = false;
				} else if (showNewTriggerModal) {
					showNewTriggerModal = false;
				}
			}
		}
		window.addEventListener('keydown', handleKeydown);

		return () => window.removeEventListener('keydown', handleKeydown);
	});

	function openPreviewModal(post: GeneratedPost, event?: MouseEvent) {
		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}

		selectedPreviewPost = { ...post };
	}

	function closePreviewModal() {
		selectedPreviewPost = null;
	}

	function navigateToFilteredLogs(ruleOrTriggerName: string) {
		logRuleFilter = ruleOrTriggerName;
		logSearchQuery = '';
		activeTab = 'logs';
	}

	function clearLogRuleFilter() {
		logRuleFilter = null;
	}

	// Dynamic Time-Aware Interactive AI Greeting Generator
	function getDynamicGreeting(): { title: string; caption: string; image: string } {
		const hour = new Date().getHours();

		if (hour >= 5 && hour < 12) {
			return {
				title: '☕ Dynamic AI Morning Greeting & Community Check-in',
				caption: `Good morning everyone! ☕ Hope you're starting your day with great energy and coffee! ✨\n\nQuick question for the group: What's your #1 goal or tech upgrade project for today? Drop it in the comments below! 👇`,
				image: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&auto=format&fit=crop&q=80'
			};
		} else if (hour >= 12 && hour < 18) {
			return {
				title: '☀️ Dynamic AI Afternoon Community Greeting',
				caption: `Happy afternoon tech family! ☀️ Hope your day is going awesome so far!\n\nQuick check-in for everyone: Are you working from home or at the office today? What setup are you typing on right now? Let us know below! 👇`,
				image: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&auto=format&fit=crop&q=80'
			};
		} else {
			return {
				title: '🌙 Dynamic AI Evening Lounge & Connection Post',
				caption: `Good evening everyone! 🌙 Time to unwind and relax after a busy day! ✨\n\nWhat's the best thing that happened to you today? Or what gadget/game are you enjoying tonight? Share your thoughts with the community below! 👇`,
				image: 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600&auto=format&fit=crop&q=80'
			};
		}
	}

	// Test & Generate Live Content Preview Before Saving (Opens Dedicated Preview Modal)
	async function testGeneratePreview() {
		if (selectedWorkflowActions.length === 0) {
			actionNotification = '⚠️ Please select at least 1 workflow action step before testing.';
			actionNotificationType = 'warning';
			actionNotificationLink = null;
			setTimeout(() => actionNotification = '', 4000);

			return;
		}

		showTestPreviewModal = true;
		isTestingWorkflow = true;
		testPreviewResult = null;

		try {
			const token = localStorage.getItem('aiffiliate_token');
			const res = await fetch(`${API_BASE}/api/workflows/execute`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': getCsrfToken(),
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					...(token ? { Authorization: `Bearer ${token
				}` } : {})
				},
				body: JSON.stringify({
					name: newRuleName.trim() || 'Test Workflow Rule',
					category: newRuleCategory,
					actions: selectedWorkflowActions,
					target_page: newRulePage,
					general_context: generalPostContext.trim(),
					weather_context: weatherContext.trim(),
					occasion_context: occasionContext.trim(),
					tones: selectedTones,
					personas: selectedPersonas,
					custom_persona: customPersonaInput.trim(),
					manual_prompt: manualCustomPrompt.trim(),
					is_preview: true
				})
			});

			const hasMediaStep = selectedWorkflowActions.some(a => {
				const l = a.toLowerCase();

				return l.includes('extract') || l.includes('shopee') || l.includes('product media');
			});

			if (res.ok) {
				const data = await res.json();
				let sampleImg = hasMediaStep ? 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&auto=format&fit=crop&q=80' : '';
				let caption = data.caption || 'Generated AI deal caption...';

				testPreviewResult = {
					title: data.title || newRuleName || 'Generated Content Preview',
					caption: caption,
					image: sampleImg,
					executedSteps: data.executed_steps || selectedWorkflowActions
				};
			} else {
				const errData = await res.json().catch(() => ({}));

				throw new Error(errData.detail || `Server returned HTTP ${res.status}`);
			}
		} catch (err) {
			console.warn('Backend preview fallback:', err);
			const hasMediaStep = selectedWorkflowActions.some(a => {
				const l = a.toLowerCase();

				return l.includes('extract') || l.includes('shopee') || l.includes('product media');
			});
			const greeting = getDynamicGreeting();

			testPreviewResult = {
				title: greeting.title,
				caption: greeting.caption,
				image: hasMediaStep ? greeting.image : '',
				executedSteps: selectedWorkflowActions
			};
		} finally {
			isTestingWorkflow = false;
		}
	}

	// Copywriting Tone Multi-Select Options
	const availableTones = [
		{ id: 'taglish', label: 'Taglish 🇵🇭', description: 'Natural Pinoy Taglish blend' },
		{ id: 'casual', label: 'Casual 🤝', description: 'Friendly conversational tone' },
		{ id: 'hype', label: 'Viral Hype 🔥', description: 'High energy and urgency' },
		{ id: 'professional', label: 'Professional 👔', description: 'Clean polished tone' },
		{ id: 'friendly', label: 'Friendly ☕', description: 'Warm and inviting' },
		{ id: 'minimalist', label: 'Minimalist ⚡', description: 'Short 3-line bullet style' }
	];

	// Creator Persona Multi-Select Options
	const availablePersonas = [
		{ id: 'storyteller', label: 'Storyteller Reviewer 📖', description: 'Relatable personal experience' },
		{ id: 'tech_reviewer', label: 'Technical Reviewer 💻', description: 'Specs breakdown and expert buying tips' },
		{ id: 'deal_hunter', label: 'Deal Hunter / Shopping Buddy 🛒', description: 'Urgent price callouts and voucher tips' },
		{ id: 'community_host', label: 'Community Host 🎙️', description: 'Engages followers with check-in questions and polls' },
		{ id: 'curator', label: 'Minimalist Curator 🎯', description: 'Curated recommendations' }
	];

	let generalPostContext = $state('');
	let weatherContext = $state('');
	let occasionContext = $state('');
	let selectedTones = $state<string[]>([]);
	let selectedPersonas = $state<string[]>([]);
	let customPersonaInput = $state('');
	let manualCustomPrompt = $state('');
	let selectedWorkflowActions = $state<string[]>([]);
	let actionContexts = $state<Record<string, string>>({});
	let canProceedToSubStep3_3 = $derived(selectedWorkflowActions.length > 0 || manualCustomPrompt.trim().length > 0);

	function toggleTone(toneId: string) {
		if (selectedTones.includes(toneId)) {
			selectedTones = selectedTones.filter(t => t !== toneId);
		} else {
			selectedTones = [...selectedTones, toneId];
		}

		testPreviewResult = null;
	}

	function togglePersona(personaId: string) {
		if (selectedPersonas.includes(personaId)) {
			selectedPersonas = selectedPersonas.filter(p => p !== personaId);
		} else {
			selectedPersonas = [...selectedPersonas, personaId];
		}

		testPreviewResult = null;
	}

	// Categorized Preset Content Generation Parameters (General Purpose + Dedicated Affiliate Specific Parameters)
	const presetWorkflows: WorkflowActionDefinition[] = [
		// General Purpose Content Parameters
		{
			id: 'dynamic_greeting',
			label: '1. Generate Dynamic Time & Date Aware Greeting',
			title: 'Dynamic Time & Date Aware Greeting Caption',
			docDescription: 'Checks local time (Morning/Afternoon/Evening) and day of week (e.g. Happy Saturday!) to generate a warm, casual community check-in.',
			inputOutputDoc: 'Input: Time & Date Context ➔ Output: Time & Day Appropriate Community Greeting.',
			contextPlaceholder: 'e.g. Happy Saturday WFH coffee check-in'
		},
		{
			id: 'weather_aware',
			label: '2. Generate Weather-Aware Lounge Callout (Sunny / Rainy Check-in)',
			title: 'Weather-Aware Lounge Check-in Callout',
			docDescription: 'Appends a local weather check-in callout (e.g., cozy rainy WFH vibes or hot sunny coffee break).',
			inputOutputDoc: 'Input: Local Weather Vibe ➔ Output: Weather-Themed Lounge Callout.',
			contextPlaceholder: 'e.g. Cozy rainy afternoon setup check-in'
		},
		{
			id: 'occasion_aware',
			label: '3. Generate Occasion & Holiday Aware Hook (Payday / Sales / Weekends)',
			title: 'Occasion & Holiday Aware Hook Generator',
			docDescription: 'Incorporate current occasion, holiday, or flash sale hooks (e.g., 8.8 Payday Sale, Weekend Lounge).',
			inputOutputDoc: 'Input: Occasion Context ➔ Output: Occasion-Themed Post Hook.',
			contextPlaceholder: 'e.g. 8.8 Payday Sale or Weekend Gaming Lounge'
		},
		{
			id: 'community_poll',
			label: '4. Generate Interactive Poll & Question Caption (A/B Voting Hook)',
			title: 'Interactive Poll & Question Caption Generator',
			docDescription: 'Generates an interactive A/B product preference comparison caption that encourages community members to vote in the comments.',
			inputOutputDoc: 'Input: Niche Context ➔ Output: Structured Poll Caption with Choice A vs Choice B callouts.',
			contextPlaceholder: 'e.g. Compare Mechanical Keyboards vs Wireless Headphones'
		},
		{
			id: 'viewer_engagement',
			label: '5. Generate Fan Discussion & Viewer Engagement Hook',
			title: 'Fan Discussion & Viewer Engagement Hook',
			docDescription: 'Appends a targeted community question or opinion callout to encourage fan comments and interaction.',
			inputOutputDoc: 'Input: Discussion Context ➔ Output: Engaging Open Question Callout for Comments.',
			contextPlaceholder: 'e.g. Ask fans what gadget they bought this month'
		},
		{
			id: 'value_tip',
			label: '6. Generate Specs & Reviewer Buying Advice Caption (Tech Analysis)',
			title: 'Reviewer Buying Advice & Specs Caption',
			docDescription: 'Creates a helpful reviewer-style caption breaking down product specs, features, and smart online buying tips.',
			inputOutputDoc: 'Input: Shopping Niche Context ➔ Output: 3-line Pro Buying Advice Caption.',
			contextPlaceholder: 'e.g. Highlight Shopee Payday Sale voucher stacking tips'
		},
		{
			id: 'brand_promo',
			label: '7. Generate Brand Discount & Voucher Showcase Caption',
			title: 'Brand Voucher & Promo Code Showcase Caption',
			docDescription: 'Appends official brand discount voucher codes (e.g. "TECHDEALS100") and promo announcement text to the caption.',
			inputOutputDoc: 'Input: Promo Voucher Code ➔ Output: Brand Voucher Announcement Caption.',
			contextPlaceholder: 'e.g. Voucher ANKER88 for 20% OFF until midnight'
		},
		{
			id: 'hashtags',
			label: '8. Merge Custom & Trending Deal Hashtags',
			title: 'Default & Custom Hashtag Cluster Merger',
			docDescription: 'Combines your page’s default global hashtags (#TechSulitDeals #ShopeePH #BudolFinds) with custom niche tags.',
			inputOutputDoc: 'Input: Post Tags ➔ Output: Formatted Hashtag Cluster.',
			contextPlaceholder: 'e.g. Add #ShopeePayDay #GadgetDeals'
		},
		{
			id: 'auto_hashtags',
			label: '6. Generate Auto AI Hashtags Based on Content',
			title: 'Auto AI Hashtags Generator',
			docDescription: 'Analyzes post title & caption text to automatically build relevant, trending hashtag clusters (#TechSulitDeals #WFHSetup #ShopeePH).',
			inputOutputDoc: 'Input: Generated Title + Caption ➔ Output: Relevant Dynamic Hashtags Appended.',
			contextPlaceholder: 'e.g. Include #PayDaySale #TechBudol'
		},

		// Dedicated Affiliate Specific Actions
		{
			id: 'affiliate_extract',
			label: '7. Extract Shopee Affiliate Product & Deal Details (Shopee Scraper)',
			title: 'Shopee Affiliate Product & Media Extraction',
			docDescription: 'Scrapes Shopee item metadata (product title, high-res images, flash sale pricing) to feed exact product details into the affiliate engine.',
			inputOutputDoc: 'Input: Shopee URL ➔ Output: Product Title, Clean Images, Price, and Affiliate Buy Link.',
			contextPlaceholder: 'e.g. https://shopee.ph/product/12345/67890'
		},
		{
			id: 'affiliate_deal_hook',
			label: '8. Generate High-Converting Affiliate Shopee Deal Hook (Price Callout & Buy CTA)',
			title: 'Affiliate Shopee Deal Hook & Copy Generator',
			docDescription: 'Composes a high-energy affiliate deal caption loaded with pricing callouts, discount urgency, and direct Shopee buy link CTA.',
			inputOutputDoc: 'Input: Shopee Product Title + Price ➔ Output: High-Converting Affiliate Deal Caption.',
			contextPlaceholder: 'e.g. Emphasize limited 50% OFF flash sale urgency'
		},
		{
			id: 'affiliate_conversion',
			label: '9. Attach Shopee Affiliate Conversion Call-to-Action & Buy Link',
			title: 'Shopee Affiliate Buy Link Attachment',
			docDescription: 'Appends official Shopee buy link callout (🛒 Official Shopee Buy Link: https://shopee.ph/...) at the bottom of the caption.',
			inputOutputDoc: 'Input: Affiliate Buy Link ➔ Output: Formatted Buy Link CTA Box.',
			contextPlaceholder: 'e.g. https://shopee.ph/product/12345/67890'
		},
		{
			id: 'fb_publish',
			label: '10. Publish Finalized Caption to Target Facebook Page',
			title: 'Facebook Graph API Caption Publisher',
			docDescription: 'Executes direct background publishing of the generated caption and media to your connected Facebook Page using official Graph API tokens.',
			inputOutputDoc: 'Input: Final Post Content + Media ➔ Output: Live Facebook Post ID & Direct Permalink URL.',
			contextPlaceholder: 'e.g. Target Page: Tech Sulit Deals'
		}
	];

	// Quick Rule Presets
	const rulePresets = [
		{
			id: 'preset_dynamic_greeting',
			icon: '💬',
			category: 'Connection & Community',
			name: 'Dynamic Time-Aware AI Greeting & Interactive Fan Post',
			description: 'Time-appropriate morning/afternoon/evening greeting asking fan engagement questions.',
			frequency: 'daily' as const,
			times: ['08:00 AM', '01:00 PM', '07:00 PM'],
			workflowActions: [
				'Generate Dynamic Time-Aware Lounge Greeting Caption'
			],
			actionContexts: {}
		},
		{
			id: 'preset_interactive_poll',
			icon: '📊',
			category: 'Connection & Community',
			name: 'Interactive Community Question & Poll',
			description: 'Engages fans with product preference polls and tech upgrade discussions.',
			frequency: 'daily' as const,
			times: ['02:00 PM', '08:00 PM'],
			workflowActions: [
				'Generate Interactive Poll & Question Caption (A/B Voting Hook)'
			],
			actionContexts: {
				'Generate Interactive Poll & Question Caption (A/B Voting Hook)': 'Compare Mechanical Keyboards vs ANC Headphones'
			}
		},
		{
			id: 'preset_brand_spotlight',
			icon: '📢',
			category: 'Brand Promotion',
			name: 'Featured Brand & Promo Code Showcase',
			description: 'Showcases official brand discounts and promo code vouchers.',
			frequency: 'weekly' as const,
			days: ['Mon', 'Thu'],
			times: ['04:00 PM'],
			workflowActions: [
				'Generate Brand Discount & Voucher Showcase Caption'
			],
			actionContexts: {
				'Generate Brand Discount & Voucher Showcase Caption': 'Use voucher ANKERTECH88 for 40% OFF'
			}
		},
		{
			id: 'preset_flash',
			icon: '⚡',
			category: 'Affiliate Deals',
			name: 'Daily Prime Flash Sale Broadcast',
			description: 'Extracts trending Shopee deals and broadcasts AI-generated deal hooks.',
			frequency: 'daily' as const,
			times: ['12:00 PM', '06:30 PM', '09:00 PM'],
			workflowActions: [
				'Extract Shopee Affiliate Product & Deal Details (Shopee Scraper)',
				'Generate High-Converting Affiliate Shopee Deal Hook (Price Callout & Buy CTA)'
			],
			actionContexts: {}
		}
	];

	const STORAGE_KEY = 'aiffiliate_scheduled_rules_v1';

	function loadRulesFromStorage(): ScheduledRule[] {
		if (typeof window === 'undefined') {
return [];
}

		try {
			const saved = localStorage.getItem(STORAGE_KEY);

			if (saved) {
				const parsed = JSON.parse(saved);

				if (Array.isArray(parsed) && parsed.length > 0) {
return parsed;
}
			}
		} catch (e) {
			console.warn('Failed to load rules from localStorage:', e);
		}

		return [
			{
				id: 'sch_dynamic_1',
				name: 'Dynamic Time-Aware AI Greeting & Interactive Fan Post',
				category: 'Connection & Community',
				frequency: 'daily',
				times: ['08:00 AM', '01:00 PM', '07:00 PM'],
				targetPage: 'Tech Sulit Deals',
				workflowActions: [
					'Generate Dynamic Time-Aware AI Greeting',
					'Publish to Facebook Page'
				],
				actionContexts: {
					'Generate Dynamic Time-Aware AI Greeting': 'Ask community members about their current coffee & WFH workstation setup'
				},
				status: 'active',
				lastRun: 'Today at 08:00 AM',
				nextRun: 'Today at 01:00 PM',
				lastGeneratedPost: {
					title: getDynamicGreeting().title,
					caption: getDynamicGreeting().caption,
					image: getDynamicGreeting().image,
					postedAt: 'Today at 08:00 AM',
					targetPage: 'Tech Sulit Deals',
					postUrl: 'https://www.facebook.com'
				}
			},
			{
				id: 'sch_2',
				name: 'Interactive Community Question & Poll',
				category: 'Connection & Community',
				frequency: 'daily',
				times: ['02:00 PM', '07:00 PM'],
				targetPage: 'Tech Sulit Deals',
				workflowActions: [
					'Generate Community Question / Poll',
					'Generate AI Caption & Engagement Hook',
					'Publish to Facebook Page'
				],
				actionContexts: {
					'Generate Community Question / Poll': 'Compare Mechanical Keyboards vs ANC Headphones'
				},
				status: 'active',
				lastRun: 'Yesterday at 07:00 PM',
				nextRun: 'Today at 2:00 PM',
				lastGeneratedPost: {
					title: '💬 Community Poll: Wireless Headphones vs Mechanical Keyboards',
					caption: 'Quick question for the group! 🔥 Which tech upgrade should we feature in our next community guide tomorrow?\n\n🅰️ ANC Wireless Headphones\n🅱️ RGB Custom Mechanical Keyboards\n\nVote in the comments! 👇',
					image: 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop&q=80',
					postedAt: 'Yesterday at 07:00 PM',
					targetPage: 'Tech Sulit Deals',
					postUrl: 'https://www.facebook.com'
				}
			},
			{
				id: 'sch_3',
				name: 'Featured Brand & Promo Code Showcase',
				category: 'Brand Promotion',
				frequency: 'weekly',
				days: ['Mon', 'Thu'],
				times: ['04:00 PM'],
				targetPage: 'Tech Sulit Deals',
				workflowActions: [
					'Extract Shopee Product Media & Title',
					'Attach Brand Promo Code & Call-to-Action',
					'Publish to Facebook Page'
				],
				actionContexts: {
					'Attach Brand Promo Code & Call-to-Action': 'Use voucher ANKERTECH88 for 40% OFF'
				},
				status: 'active',
				lastRun: 'Monday at 04:00 PM',
				nextRun: 'Thursday at 04:00 PM',
				lastGeneratedPost: {
					title: '📢 Anker Official Brand Spotlight',
					caption: '⚡ EXCLUSIVE BRAND SPOTLIGHT! Grab 40% OFF Anker Soundcore Headphones with voucher code "ANKERTECH88"!\n\n🛒 Buy Link: https://shope.ee/anker-q30-deal\n\n#AnkerPH #TechDeals #ShopeeFinds',
					image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
					postedAt: 'Monday at 04:00 PM',
					targetPage: 'Tech Sulit Deals',
					postUrl: 'https://www.facebook.com'
				}
			}
		];
	}

	function saveRulesToStorage(rules: ScheduledRule[]) {
		if (typeof window === 'undefined') {
return;
}

		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(rules));
		} catch (e) {
			console.warn('Failed to save rules to localStorage:', e);
		}
	}

	// Scheduled Rules Data with localStorage persistence
	let scheduledRules = $state<ScheduledRule[]>(loadRulesFromStorage());

	// Event Triggers Data
	let eventTriggers = $state<EventTrigger[]>([
		{
			id: 'evt_1',
			name: 'Shopee Price Drop > 40% OFF Alert',
			eventSource: 'Shopee Price Watcher',
			condition: 'Discount 40% or higher',
			targetPage: 'Tech Sulit Deals',
			action: 'Auto Extract & Instant Publish',
			status: 'active',
			totalFired: 42
		},
		{
			id: 'evt_2',
			name: 'Inbound Telegram Webhook',
			eventSource: 'Telegram Bot Webhook',
			condition: 'When new link arrives',
			targetPage: 'Tech Sulit Deals',
			action: 'Create Draft & Broadcast',
			status: 'active',
			totalFired: 128
		},
		{
			id: 'evt_3',
			name: 'Fan Comment Keyword Response Auto-Reply',
			eventSource: 'Facebook Page Webhook',
			condition: 'Comment contains "PM" or "LINK"',
			targetPage: 'Tech Sulit Deals',
			action: 'Auto DM Promo Code & Link',
			status: 'active',
			totalFired: 89
		}
	]);

	// Automation Logs Data
	let logs = $state<AutomationLog[]>([
		{
			id: 'log_100',
			timestamp: '2026-08-06 08:00:00',
			type: 'scheduled',
			ruleName: 'Dynamic Time-Aware AI Greeting & Interactive Fan Post',
			dealTitle: 'Dynamic Time-Aware AI Interactive Greeting Post',
			targetPage: 'Tech Sulit Deals',
			status: 'SUCCESS'
		},
		{
			id: 'log_101',
			timestamp: '2026-08-06 12:00:04',
			type: 'scheduled',
			ruleName: 'Interactive Community Question & Poll',
			dealTitle: 'Which gadget brand should we review next week?',
			targetPage: 'Tech Sulit Deals',
			status: 'SUCCESS'
		},
		{
			id: 'log_102',
			timestamp: '2026-08-06 14:15:30',
			type: 'event',
			ruleName: 'Shopee Price Drop > 40% OFF Alert',
			dealTitle: 'Auto Extract & Instant Publish: Anker Fast Charger 50% OFF',
			targetPage: 'Tech Sulit Deals',
			status: 'SUCCESS'
		},
		{
			id: 'log_103',
			timestamp: '2026-08-05 16:00:00',
			type: 'scheduled',
			ruleName: 'Featured Brand & Promo Code Showcase',
			dealTitle: 'Anker Official Brand Spotlight with Voucher',
			targetPage: 'Tech Sulit Deals',
			status: 'SUCCESS'
		}
	]);

	// Filtered Logs derived computation
	let filteredLogs = $derived(
		logs.filter(log => {
			if (logRuleFilter && !log.ruleName.toLowerCase().includes(logRuleFilter.toLowerCase())) {
				return false;
			}

			if (logSearchQuery.trim()) {
				const q = logSearchQuery.toLowerCase().trim();
				const matchesName = log.ruleName.toLowerCase().includes(q);
				const matchesTitle = log.dealTitle.toLowerCase().includes(q);
				const matchesPage = log.targetPage.toLowerCase().includes(q);

				if (!matchesName && !matchesTitle && !matchesPage) {
return false;
}
			}

			if (logTypeFilter !== 'all' && log.type !== logTypeFilter) {
				return false;
			}

			if (logStatusFilter !== 'all' && log.status !== logStatusFilter) {
				return false;
			}

			return true;
		})
	);

	// Custom In-DOM Dropdowns State
	let categoryDropdownOpen = $state(false);
	let frequencyDropdownOpen = $state(false);
	let pageDropdownOpen = $state(false);
	let triggerSourceDropdownOpen = $state(false);

	// Dynamic Connected Social Media Accounts State
	let connectedAccounts = $state<{ id: string; name: string; platform: string; is_enabled: boolean }[]>([]);

	// Track executed rule minutes to prevent duplicate runs within the same minute
	const executedRuleMinutes = new Map<string, number>();

	function parseTimeToMinutes(timeStr: string): number | null {
		if (!timeStr) {
return null;
}

		const str = timeStr.trim().toUpperCase();
		const match12 = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/);

		if (match12) {
			let h = parseInt(match12[1], 10);
			const m = parseInt(match12[2], 10);
			const ampm = match12[3];

			if (ampm === 'PM' && h < 12) {
h += 12;
}

			if (ampm === 'AM' && h === 12) {
h = 0;
}

			return h * 60 + m;
		}

		const match24 = str.match(/^(\d{1,2}):(\d{2})$/);

		if (match24) {
			const h = parseInt(match24[1], 10);
			const m = parseInt(match24[2], 10);

			return h * 60 + m;
		}

		return null;
	}

	// Real-Time WebSocket Task Progress State & Live Countdown Ticker
	let wsConnectedState = $state(false);
	let currentTaskProgress = $state<{ taskId: string; step: string; progress: number; detail: string } | null>(null);
	let currentTimeTicker = $state(Date.now());

	function getNextRunCountdown(rule: ScheduledRule): { text: string; detail: string } {
		if (rule.status === 'disabled') {
			return { text: '⏸ Rule Paused', detail: 'Enable rule to resume schedule' };
		}

		if (!rule.times || rule.times.length === 0) {
			return { text: '⏰ Scheduled Daily', detail: 'Runs automatically' };
		}

		const now = new Date(currentTimeTicker);
		const currentMinuteOfDay = now.getHours() * 60 + now.getMinutes();
		const currentSeconds = now.getSeconds();

		const timeMinutes = rule.times.map(parseTimeToMinutes).filter(m => m !== null) as number[];

		if (timeMinutes.length === 0) {
			return { text: '⏰ Scheduled Daily', detail: 'Runs automatically' };
		}

		timeMinutes.sort((a, b) => a - b);

		let nextMinute = timeMinutes.find(m => m > currentMinuteOfDay);
		let isTomorrow = false;

		if (nextMinute === undefined) {
			nextMinute = timeMinutes[0];
			isTomorrow = true;
		}

		let minutesUntil = isTomorrow
			? (1440 - currentMinuteOfDay) + nextMinute
			: nextMinute - currentMinuteOfDay;

		const totalSecondsRemaining = minutesUntil * 60 - currentSeconds;

		if (totalSecondsRemaining <= 0) {
			return { text: '⚡ Executing Now...', detail: 'Workflow in progress' };
		}

		const hours = Math.floor(totalSecondsRemaining / 3600);
		const minutes = Math.floor((totalSecondsRemaining % 3600) / 60);
		const seconds = totalSecondsRemaining % 60;

		const hh = hours > 0 ? `${hours}h ` : '';
		const mm = `${minutes.toString().padStart(2, '0')}m `;
		const ss = `${seconds.toString().padStart(2, '0')}s`;

		const timeStr = rule.times.find(t => parseTimeToMinutes(t) === nextMinute) || '';

		return {
			text: `⏳ Next Run in: ${hh}${mm}${ss}`,
			detail: `Scheduled for ${timeStr} ${isTomorrow ? '(Tomorrow)' : '(Today)'}`
		};
	}

	function getEvaluatedPromptPreview(): { prompt: string; hints: string[] } {
		const now = new Date(currentTimeTicker);
		const hours = now.getHours();
		let timeTag = 'Morning ☕';

		if (hours >= 12 && hours < 18) {
timeTag = 'Afternoon ☀️';
} else if (hours >= 18 || hours < 5) {
timeTag = 'Evening 🌙';
}

		const dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];
		const dateStr = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

		const promptLines: string[] = [
			`[DYNAMIC AWARENESS]\n• Time of Day: ${timeTag}\n• Date: ${dayName}, ${dateStr}`,
			generalPostContext.trim() ? `[GLOBAL THEME]\n${generalPostContext.trim()}` : '',
			weatherContext.trim() ? `[WEATHER VIBE]\n${weatherContext.trim()}` : '',
			occasionContext.trim() ? `[OCCASION/SALE]\n${occasionContext.trim()}` : '',
			selectedTones.length > 0 ? `[COPYWRITING TONES]\n${selectedTones.join(', ')}` : '',
			selectedPersonas.length > 0 ? `[CREATOR PERSONAS]\n${selectedPersonas.join(', ')}` : '',
			customPersonaInput.trim() ? `[CUSTOM VOICE DIRECTIVES]\n${customPersonaInput.trim()}` : '',
			selectedWorkflowActions.length > 0 ? `[ACTION DIRECTIVES]\n${selectedWorkflowActions.join('\n')}` : '',
			manualCustomPrompt.trim() ? `[MANUAL PROMPT INSTRUCTIONS]\n${manualCustomPrompt.trim()}` : ''
		].filter(Boolean);

		const hints: string[] = [];

		if (!generalPostContext.trim()) {
hints.push('💡 Add a Global Topic Context (e.g. WFH coffee setup) to anchor captions to your niche.');
}

		if (!weatherContext.trim()) {
hints.push('💡 Adding Weather Vibe (e.g. Cozy rainy afternoon) increases viewer relatability.');
}

		if (!occasionContext.trim()) {
hints.push('💡 Add Occasion/Sale Context (e.g. 8.8 Payday Sale) to build urgency.');
}

		if (selectedTones.length === 0) {
hints.push('💡 Select a copywriting tone like "Taglish 🇵🇭" or "Casual 🤝" for an authentic social voice.');
}

		if (selectedPersonas.length === 0) {
hints.push('💡 Select a Creator Persona (e.g. Storyteller Reviewer) to give posts distinct personality.');
}

		return {
			prompt: promptLines.join('\n\n'),
			hints: hints.length > 0 ? hints : ['✨ Prompt parameters are fully evaluated and ready for AI generation!']
		};
	}

	function getEventTriggerHintAndPayload(): { hint: string; examplePayload: string } {
		if (newTriggerSource === 'Shopee Price Watcher' || newTriggerAction === 'Auto Extract & Instant Publish') {
			return {
				hint: '💡 Listens for price drop webhooks or Shopee URL scrapers. Evaluates discount % against your threshold before generating AI deal posts.',
				examplePayload: JSON.stringify({
					event: "SHOPEE_PRICE_DROP_ALERT",
					product_name: "Baseus 65W GaN Charger",
					old_price: "₱2,499",
					new_price: "₱1,199",
					discount: "52% OFF",
					url: triggerShopeeUrl || "https://s.shopee.ph/60QPnzrXoO"
				}, null, 2)
			};
		} else if (newTriggerSource === 'Inbound Telegram Bot') {
			return {
				hint: '💡 Scrapes Shopee URLs from Telegram channel messages. Automatically extracts high-res product photos and item pricing before generating AI captions.',
				examplePayload: JSON.stringify({
					source: "Telegram Channel Bot",
					chat_title: "Tech Deals PH",
					message_text: "🔥 Check out this deal! " + (triggerShopeeUrl || "https://s.shopee.ph/60QPnzrXoO"),
					extracted_url: triggerShopeeUrl || "https://s.shopee.ph/60QPnzrXoO"
				}, null, 2)
			};
		} else if (newTriggerSource === 'Facebook Page Comments' || newTriggerAction === 'Fan Comment Auto-Reply') {
			return {
				hint: `💡 Evaluates incoming fan comments against keywords (${triggerKeywords || 'HM, LINK, PM'}). Automatically dispatches personalized buy-link replies via Graph API.`,
				examplePayload: JSON.stringify({
					event: "FAN_COMMENT_AUTO_REPLY",
					comment_id: "comment_98765",
					user_name: "Maria Santos",
					comment_text: "HM link please",
					matched_keyword: "hm",
					auto_reply_template: triggerReplyTemplate || "Hi {user_name}! Here is the buy link: {buy_link}"
				}, null, 2)
			};
		} else if (newTriggerAction === 'n8n Outbound Webhook Dispatch') {
			return {
				hint: `💡 Dispatches JSON payloads to your n8n workflow endpoint (${triggerWebhookUrl || 'https://n8n.example.com'}) whenever new deal posts are generated.`,
				examplePayload: JSON.stringify({
					event: "post.published",
					webhook_target: triggerWebhookUrl || "https://n8n.example.com/webhook/deal-alert",
					secret_auth: triggerWebhookSecret ? "*******" : "none",
					post: {
						title: "Baseus 65W GaN Charger",
						caption: "🔥 Deal alert! Check out Baseus 65W GaN Charger..."
					}
				}, null, 2)
			};
		} else {
			return {
				hint: `💡 Evaluates voucher code expiry warnings (${triggerVoucherCode || 'ANKERTECH88'}) before creating urgency flash sale captions.`,
				examplePayload: JSON.stringify({
					event: "VOUCHER_EXPIRY_ALERT",
					voucher_code: triggerVoucherCode || "ANKERTECH88",
					discount: triggerVoucherDiscount || "40% OFF",
					target_page: newTriggerTargetPage
				}, null, 2)
			};
		}
	}

	function getIntegrationInstructions(): { endpoint: string; instructions: string[] } {
		if (newTriggerSource === 'Inbound Telegram Bot') {
			return {
				endpoint: 'POST http://localhost:8176/api/webhooks/telegram',
				instructions: [
					'1. Set your Telegram Bot webhook URL to: http://localhost:8176/api/webhooks/telegram',
					'2. Add your Telegram Bot to your deal channel or group as an Admin.',
					'3. Whenever a message containing a Shopee link (s.shopee.ph) is posted in Telegram, Aiffiliate automatically extracts images, title, and posts to Facebook!'
				]
			};
		} else if (newTriggerSource === 'Facebook Page Comments' || newTriggerAction === 'Fan Comment Auto-Reply') {
			return {
				endpoint: 'POST http://localhost:8176/api/webhooks/facebook/comment',
				instructions: [
					'1. In Meta App Dashboard ➔ Webhooks ➔ Select Page "feed" or "comments" topic.',
					'2. Set Callback URL to: http://localhost:8176/api/webhooks/facebook/comment',
					'3. When fans comment "HM", "LINK", or "PM", Aiffiliate automatically replies with your affiliate buy link via Graph API.'
				]
			};
		} else if (newTriggerSource === 'Shopee Price Watcher' || newTriggerAction === 'Auto Extract & Instant Publish') {
			return {
				endpoint: 'POST http://localhost:8176/api/webhooks/shopee-price-drop',
				instructions: [
					'1. Configure your price monitor script or n8n workflow HTTP Request node.',
					'2. Set Request URL to: http://localhost:8176/api/webhooks/shopee-price-drop',
					'3. Pass JSON payload: {"product_name": "Baseus Charger", "old_price": "₱2,499", "new_price": "₱1,199", "discount": "52% OFF"}.'
				]
			};
		} else {
			return {
				endpoint: 'POST http://localhost:8176/api/webhooks/test',
				instructions: [
					'1. Configure n8n Outbound Webhook URL in Settings (/settings).',
					'2. Whenever posts are published, Aiffiliate dispatches an outbound JSON payload to your n8n workflow.',
					'3. Click "Send Test Webhook" in Settings to test connectivity.'
				]
			};
		}
	}

	onMount(() => {
		// 1-second live countdown ticker
		const tickerInterval = setInterval(() => {
			currentTimeTicker = Date.now();
		}, 1000);
		// Subscribe to backend WebSocket real-time events
		const unsubscribeWs = wsClient.subscribe((event: WsTaskProgressEvent) => {
			wsConnectedState = wsClient.isConnected;

			if (event.event === 'WORKFLOW_STEP_PROGRESS') {
				currentTaskProgress = {
					taskId: event.task_id || '',
					step: event.step || '',
					progress: event.progress || 0,
					detail: event.detail || ''
				};
			} else if (event.event === 'TASK_COMPLETED') {
				currentTaskProgress = null;
				const resData = event.result || event;
				const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);
				const liveLink = event.post_url || event.link || resData.post_url || resData.link || resData.live_post_url || 'https://www.facebook.com';

				logs = [
					{
						id: `log_ws_${Date.now()}`,
						timestamp: nowStr,
						type: 'manual',
						ruleName: resData.rule_name || event.rule_name || 'Background Workflow',
						dealTitle: resData.title || 'Automated Post',
						targetPage: resData.target_page || 'Tech Sulit Deals',
						status: 'SUCCESS',
						postUrl: liveLink,
						caption: resData.caption || ''
					},
					...logs
				];

				actionNotification = event.message || `✅ Dynamic AI Workflow Executed & Created Post for "${resData.rule_name || 'Workflow'}"!`;
				actionNotificationType = 'success';
				actionNotificationLink = liveLink;
				setTimeout(() => actionNotification = '', 12000);
			} else if (event.event === 'TASK_FAILED') {
				currentTaskProgress = null;
				actionNotification = `⚠️ Task Error: ${event.error || 'Background execution failed'}`;
				actionNotificationType = 'warning';
				actionNotificationLink = null;
				setTimeout(() => actionNotification = '', 6000);
			}
		});

		fetch('/settings/social-accounts').then(r => r.json()).catch(() => [])
			.then(res => {
				const list = Array.isArray(res) ? res : ((res as any)?.integrations || []);
				connectedAccounts = list.filter((a: any) => a.is_enabled);
			})
			.catch(err => console.warn('Could not load connected social accounts:', err));

		// Automatic Background Rule Scheduler Ticker (Checks active scheduled rules every 10 seconds)
		const schedulerInterval = setInterval(() => {
			const now = new Date();
			const currentMinuteOfDay = now.getHours() * 60 + now.getMinutes();
			const currentDay = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][now.getDay()];

			for (const rule of scheduledRules) {
				if (rule.status !== 'active') {
continue;
}

				// Skip if already executed for this minute
				if (executedRuleMinutes.get(rule.id) === currentMinuteOfDay) {
continue;
}

				const timeMinutesList = (rule.times || []).map(parseTimeToMinutes).filter(m => m !== null);
				const matchesTime = timeMinutesList.includes(currentMinuteOfDay);

				if (!matchesTime) {
continue;
}

				if (rule.frequency === 'daily' || (rule.frequency === 'weekly' && rule.days?.includes(currentDay))) {
					if (!runningRuleId) {
						executedRuleMinutes.set(rule.id, currentMinuteOfDay);
						console.log(`⏰ [AutoScheduler] Triggering scheduled rule "${rule.name}" at minute ${currentMinuteOfDay}...`);
						runScheduleNow(rule);
					}
				}
			}
		}, 10000);

		return () => {
			unsubscribeWs();
			clearInterval(schedulerInterval);
			clearInterval(tickerInterval);
		};
	});

	const categoryOptions = [
		{ value: 'Connection & Community', label: '🤝 Connection & Community (Dynamic Greetings, Polls)' },
		{ value: 'Brand Promotion', label: '📢 Brand Promotion (Spotlights, Vouchers)' },
		{ value: 'Affiliate Deals', label: '🛒 Affiliate Deals (Shopee Flash Sales)' }
	];

	const frequencyOptions = [
		{ value: 'daily', label: 'Daily (Multiple Specific Times)' },
		{ value: 'interval', label: 'Recurring Interval (e.g. Every N Hours)' },
		{ value: 'weekly', label: 'Specific Days of the Week' }
	];

	let pageOptions = $derived([
		{ value: 'Tech Sulit Deals', label: '📘 Tech Sulit Deals (Default Facebook Page)' },
		...connectedAccounts.filter(a => a.name !== 'Tech Sulit Deals').map(a => ({
			value: a.name,
			label: `${a.platform === 'facebook' ? '📘' : a.platform === 'instagram' ? '📸' : '🌐'} ${a.name}`
		})),
		{ value: 'All Connected Target Accounts', label: '🚀 All Active Connected Accounts (Multi-Publish)' }
	]);

	const triggerSourceOptions = [
		{ value: 'Shopee Price Watcher', label: 'Shopee Price Watcher' },
		{ value: 'Telegram Bot Webhook', label: 'Telegram Bot Webhook' },
		{ value: 'Facebook Page Webhook', label: 'Facebook Page Comment Auto-Reply' }
	];

	// Modal Form State
	let showNewScheduleModal = $state(false);
	let editingRuleId = $state<string | null>(null);
	let subStep3 = $state<1 | 2 | 3>(1);
	let newRuleName = $state('');
	let newRuleCategory = $state<'Connection & Community' | 'Brand Promotion' | 'Affiliate Deals' | string>('Connection & Community');
	let newRuleFrequency = $state<'daily' | 'interval' | 'weekly' | string>('daily');
	let manualTimeSlots = $state<string[]>(['08:00 AM', '01:00 PM', '07:00 PM']);
	let nativeTimeInput = $state('08:00');
	let selectedInterval = $state<number>(4);
	let selectedDays = $state<string[]>(['Mon', 'Wed', 'Fri']);
	let newRulePage = $state('Tech Sulit Deals');

	let showNewTriggerModal = $state(false);
	let newTriggerName = $state('');
	let newTriggerSource = $state('Shopee Price Watcher');
	let newTriggerCondition = $state('Discount >= 30%');

	const dayOptions = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

	function applyPreset(preset: typeof rulePresets[0]) {
		newRuleName = preset.name;
		newRuleCategory = preset.category as any;
		newRuleFrequency = preset.frequency as any;
		manualTimeSlots = [...preset.times];
		selectedWorkflowActions = [...preset.workflowActions];
		actionContexts = preset.actionContexts ? ({ ...preset.actionContexts } as Record<string, string>) : {};
		testPreviewResult = null;
		actionNotification = `Applied template: ${preset.icon} ${preset.name}`;
		actionNotificationType = 'info';
		actionNotificationLink = null;
		setTimeout(() => actionNotification = '', 3000);
	}

	function toggleWorkflowAction(cmdLabel: string) {
		const cleanLabel = cmdLabel.replace(/^\d+\.\s*/, '');

		if (selectedWorkflowActions.includes(cleanLabel)) {
			if (selectedWorkflowActions.length > 1) {
				selectedWorkflowActions = selectedWorkflowActions.filter(c => c !== cleanLabel);
			}
		} else {
			selectedWorkflowActions = [...selectedWorkflowActions, cleanLabel];
		}

		testPreviewResult = null;
	}

	function toggleScheduleStatus(id: string) {
		scheduledRules = scheduledRules.map(r => {
			if (r.id === id) {
				const nextStatus = r.status === 'active' ? 'disabled' : 'active';
				actionNotification = `Rule "${r.name}" is now ${nextStatus.toUpperCase()}`;
				actionNotificationType = 'info';
				actionNotificationLink = null;
				setTimeout(() => actionNotification = '', 3000);

				return { ...r, status: nextStatus };
			}

			return r;
		});
		saveRulesToStorage(scheduledRules);
	}

	function toggleTriggerStatus(id: string) {
		eventTriggers = eventTriggers.map(t => {
			if (t.id === id) {
				const nextStatus = t.status === 'active' ? 'disabled' : 'active';
				actionNotification = `Trigger "${t.name}" is now ${nextStatus.toUpperCase()}`;
				actionNotificationType = 'info';
				actionNotificationLink = null;
				setTimeout(() => actionNotification = '', 3000);

				return { ...t, status: nextStatus };
			}

			return t;
		});
	}

	// Real Backend Execution via FastAPI AI Caption Generator + Alert Link
	async function runScheduleNow(rule: ScheduledRule) {
		if (runningRuleId) {
return;
}

		runningRuleId = rule.id;
		activeStepIndex = 0;

		const totalSteps = rule.workflowActions.length;
		let currentStep = 0;

		const stepInterval = setInterval(() => {
			currentStep += 1;

			if (currentStep < totalSteps) {
				activeStepIndex = currentStep;
			} else {
				clearInterval(stepInterval);
			}
		}, 450);

		try {
			let generatedTitle = `✨ Published Post: ${rule.name}`;
			let generatedCaption = `Hello ${rule.targetPage} community! Here is your automated update.`;
			let livePostUrl = 'https://www.facebook.com';

			const token = localStorage.getItem('aiffiliate_token');
			const res = await fetch(`${API_BASE}/api/workflows/execute`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': getCsrfToken(),
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					...(token ? { Authorization: `Bearer ${token
				}` } : {})
				},
				body: JSON.stringify({
					name: rule.name,
					category: rule.category,
					actions: rule.workflowActions,
					target_page: rule.targetPage,
					general_context: rule.generalContext || '',
					weather_context: rule.weatherContext || '',
					occasion_context: rule.occasionContext || '',
					tones: rule.tones || [],
					personas: rule.personas || [],
					custom_persona: rule.customPersona || '',
					manual_prompt: rule.manualPrompt || '',
					is_preview: false
				})
			});

			if (!res.ok) {
				const errData = await res.json().catch(() => ({}));

				throw new Error(errData.detail || `Server returned HTTP ${res.status}`);
			}

			const data = await res.json();

			if (data.title) {
generatedTitle = data.title;
}

			if (data.caption) {
generatedCaption = data.caption;
}

			if (data.post_url) {
				livePostUrl = data.post_url;
			} else if (data.live_post_url) {
				livePostUrl = data.live_post_url;
			}

			const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);

			const newPost: GeneratedPost = {
				title: generatedTitle,
				caption: generatedCaption,
				image: '',
				postedAt: 'Just now',
				targetPage: rule.targetPage,
				postUrl: livePostUrl
			};

			logs = [
				{
					id: `log_manual_${Date.now()}`,
					timestamp: nowStr,
					type: 'manual',
					ruleName: rule.name,
					dealTitle: newPost.title,
					targetPage: rule.targetPage,
					status: 'SUCCESS',
					postUrl: livePostUrl,
					caption: generatedCaption
				},
				...logs
			];

			scheduledRules = scheduledRules.map(r => r.id === rule.id ? {
				...r,
				lastRun: 'Just now',
				lastGeneratedPost: newPost
			} : r);
			saveRulesToStorage(scheduledRules);

			actionNotification = `✅ Dynamic AI Workflow Executed & Created Post for "${rule.name}"!`;
			actionNotificationType = 'success';
			actionNotificationLink = livePostUrl;
		} catch (err: any) {
			console.error('Failed to publish post to backend API:', err);
			actionNotification = `⚠️ Workflow Execution Failed: ${err.message || 'Authentication error'}`;
			actionNotificationType = 'warning';
			actionNotificationLink = null;
		} finally {
			runningRuleId = null;
			activeStepIndex = -1;
			setTimeout(() => actionNotification = '', 8000);
		}
	}

	async function runTriggerNow(trigger: EventTrigger) {
		if (runningTriggerId) {
return;
}

		runningTriggerId = trigger.id;

		try {
			let generatedTitle = `⚡ Event Fired: ${trigger.name}`;
			let generatedCaption = `🔥 Auto Event Alert: ${trigger.condition} triggered for ${trigger.targetPage}!`;
			let livePostUrl = 'https://www.facebook.com';

			const token = localStorage.getItem('aiffiliate_token');
			const res = await fetch(`${API_BASE}/api/workflows/execute`, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': getCsrfToken(),
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					...(token ? { Authorization: `Bearer ${token
				}` } : {})
				},
				body: JSON.stringify({
					name: trigger.name,
					category: 'Brand Promotion',
					actions: [
						'1. Generate Dynamic Time & Date Aware Greeting',
						'7. Generate Brand Discount & Voucher Showcase Caption',
						'Publish Finalized Caption to Target Facebook Page'
					],
					target_page: trigger.targetPage,
					general_context: `${trigger.name} - ${trigger.condition}`,
					weather_context: '',
					occasion_context: trigger.condition,
					tones: ['Taglish 🇵🇭', 'Viral Hype 🔥'],
					personas: ['Technical Reviewer 💻'],
					is_preview: false
				})
			});

			if (!res.ok) {
				const errData = await res.json().catch(() => ({}));

				throw new Error(errData.detail || `Server returned HTTP ${res.status}`);
			}

			const data = await res.json();

			if (data.title) {
generatedTitle = data.title;
}

			if (data.caption) {
generatedCaption = data.caption;
}

			if (data.post_url) {
				livePostUrl = data.post_url;
			} else if (data.live_post_url) {
				livePostUrl = data.live_post_url;
			}

			const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);
			logs = [
				{
					id: `log_event_${Date.now()}`,
					timestamp: nowStr,
					type: 'event',
					ruleName: trigger.name,
					dealTitle: generatedTitle,
					targetPage: trigger.targetPage,
					status: 'SUCCESS',
					postUrl: livePostUrl,
					caption: generatedCaption
				},
				...logs
			];

			eventTriggers = eventTriggers.map(t => t.id === trigger.id ? { ...t, totalFired: t.totalFired + 1 } : t);
			actionNotification = `✅ Event Trigger Executed & Created Post for "${trigger.name}"!`;
			actionNotificationType = 'success';
			actionNotificationLink = livePostUrl;
		} catch (err: any) {
			console.error('Event trigger execution failed:', err);
			actionNotification = `⚠️ Event Execution Failed: ${err.message || 'Authentication error'}`;
			actionNotificationType = 'warning';
			actionNotificationLink = null;
		} finally {
			runningTriggerId = null;
			setTimeout(() => actionNotification = '', 8000);
		}
	}

	function format24to12(time24: string): string {
		if (!time24) {
return '12:00 PM';
}

		const [hStr, mStr] = time24.split(':');
		let h = parseInt(hStr, 10);
		const m = mStr || '00';
		const ampm = h >= 12 ? 'PM' : 'AM';
		h = h % 12;

		if (h === 0) {
h = 12;
}

		const formattedH = h < 10 ? `0${h}` : `${h}`;

		return `${formattedH}:${m} ${ampm}`;
	}

	function getNext30MinSlot(time24: string): string {
		const [hStr, mStr] = time24.split(':');
		let h = parseInt(hStr, 10);
		let m = int(mStr) || 0;
		m += 30;

		if (m >= 60) {
			m -= 60;
			h = (h + 1) % 24;
		}

		const hPad = h < 10 ? `0${h}` : `${h}`;
		const mPad = m < 10 ? `0${m}` : `${m}`;

		return `${hPad}:${mPad}`;
	}

	function int(v: string | undefined): number {
		return v ? parseInt(v, 10) : 0;
	}

	function addTimeFromSelector() {
		let current24 = nativeTimeInput;
		let formatted12 = format24to12(current24);

		while (manualTimeSlots.includes(formatted12)) {
			current24 = getNext30MinSlot(current24);
			formatted12 = format24to12(current24);
		}

		manualTimeSlots = [...manualTimeSlots, formatted12];
		nativeTimeInput = getNext30MinSlot(current24);
	}

	function removeTimeSlot(index: number) {
		manualTimeSlots = manualTimeSlots.filter((_, i) => i !== index);
	}

	function toggleDay(day: string) {
		if (selectedDays.includes(day)) {
			selectedDays = selectedDays.filter(d => d !== day);
		} else {
			selectedDays = [...selectedDays, day];
		}
	}

	function openAddScheduleModal() {
		editingRuleId = null;
		wizardStep = 1;
		subStep3 = 1;
		newRuleName = '';
		newRuleCategory = 'Connection & Community';
		newRuleFrequency = 'daily';
		manualTimeSlots = ['08:00 AM', '01:00 PM', '07:00 PM'];
		nativeTimeInput = '08:00';
		selectedInterval = 4;
		selectedDays = ['Mon', 'Wed', 'Fri'];
		selectedWorkflowActions = [];
		actionContexts = {};
		generalPostContext = '';
		weatherContext = '';
		occasionContext = '';
		selectedTones = [];
		selectedPersonas = [];
		customPersonaInput = '';
		manualCustomPrompt = '';
		testPreviewResult = null;
		showTestPreviewModal = false;
		newRulePage = 'Tech Sulit Deals';
		showNewScheduleModal = true;
	}

	function openEditScheduleModal(rule: ScheduledRule) {
		editingRuleId = rule.id;
		wizardStep = 2;
		subStep3 = 1;
		newRuleName = rule.name;
		newRuleCategory = rule.category as any;
		newRuleFrequency = rule.frequency as any;
		manualTimeSlots = [...rule.times];
		selectedInterval = rule.intervalHours || 4;
		selectedDays = rule.days ? [...rule.days] : ['Mon', 'Wed', 'Fri'];
		selectedWorkflowActions = [...rule.workflowActions];
		actionContexts = rule.actionContexts ? { ...rule.actionContexts } : {};
		generalPostContext = rule.generalContext || '';
		weatherContext = rule.weatherContext || '';
		occasionContext = rule.occasionContext || '';
		selectedTones = rule.tones ? [...rule.tones] : [];
		selectedPersonas = rule.personas ? [...rule.personas] : [];
		customPersonaInput = rule.customPersona || '';
		manualCustomPrompt = rule.manualPrompt || '';
		testPreviewResult = null;
		showTestPreviewModal = false;
		newRulePage = rule.targetPage;
		showNewScheduleModal = true;
	}

	function deleteScheduleRule(ruleId: string, ruleName: string) {
		if (confirm(`Are you sure you want to delete the schedule rule "${ruleName}"?`)) {
			scheduledRules = scheduledRules.filter(r => r.id !== ruleId);
			saveRulesToStorage(scheduledRules);

			try {
				const token = localStorage.getItem('aiffiliate_token');
				fetch(`${API_BASE}/api/workflows/rules/${ruleId}`, {
					method: 'DELETE',
					headers: {
					'X-CSRF-TOKEN': getCsrfToken(),
					'Accept': 'application/json',
						...(token ? { Authorization: `Bearer ${token
				}` } : {})
					}
				});
			} catch (e) {
				console.warn('Backend delete sync warning:', e);
			}

			actionNotification = `Deleted rule "${ruleName}"`;
			actionNotificationType = 'warning';
			actionNotificationLink = null;
			setTimeout(() => actionNotification = '', 3000);
		}
	}

	function handleSaveSchedule() {
		if (!newRuleName.trim()) {
return;
}

		let timesToSave = manualTimeSlots.filter(t => t.trim().length > 0);

		if (timesToSave.length === 0) {
			timesToSave = ['08:00 AM'];
		}
			if (newRuleFrequency === 'interval') {
			timesToSave = [`Every ${selectedInterval} Hours`];
		}

		let actionsToSave = [...selectedWorkflowActions];
		const fbPublishAction = 'Publish Finalized Caption to Target Facebook Page';

		if (!actionsToSave.some(a => a.toLowerCase().includes('publish') || a.toLowerCase().includes('facebook'))) {
			actionsToSave.push(fbPublishAction);
		}

		let savedRuleObject: ScheduledRule | null = null;

		if (editingRuleId) {
			scheduledRules = scheduledRules.map(r => {
				if (r.id === editingRuleId) {
					const updatedRule: ScheduledRule = {
						...r,
						name: newRuleName.trim(),
						category: newRuleCategory,
						frequency: newRuleFrequency,
						times: timesToSave,
						intervalHours: newRuleFrequency === 'interval' ? selectedInterval : undefined,
						days: newRuleFrequency === 'weekly' ? selectedDays : undefined,
						targetPage: newRulePage,
						workflowActions: actionsToSave,
						actionContexts: { ...actionContexts },
						generalContext: generalPostContext.trim(),
						weatherContext: weatherContext.trim(),
						occasionContext: occasionContext.trim(),
						tones: [...selectedTones],
						personas: [...selectedPersonas],
						customPersona: customPersonaInput.trim(),
						manualPrompt: manualCustomPrompt.trim()
					};
					savedRuleObject = updatedRule;
					return updatedRule;
				}

				return r;
			});
			actionNotification = `Updated rule "${newRuleName.trim()}"`;
		} else {
			const newRule: ScheduledRule = {
				id: `sch_${Date.now()}`,
				name: newRuleName.trim(),
				category: newRuleCategory,
				frequency: newRuleFrequency,
				times: timesToSave,
				intervalHours: newRuleFrequency === 'interval' ? selectedInterval : undefined,
				days: newRuleFrequency === 'weekly' ? selectedDays : undefined,
				targetPage: newRulePage,
				workflowActions: actionsToSave,
				actionContexts: { ...actionContexts },
				generalContext: generalPostContext.trim(),
				weatherContext: weatherContext.trim(),
				occasionContext: occasionContext.trim(),
				tones: [...selectedTones],
				personas: [...selectedPersonas],
				customPersona: customPersonaInput.trim(),
				manualPrompt: manualCustomPrompt.trim(),
				status: 'active',
				lastRun: 'Never',
				nextRun: 'Scheduled'
			};
			savedRuleObject = newRule;
			scheduledRules = [newRule, ...scheduledRules];
			actionNotification = `Added rule "${newRuleName.trim()}"`;
		}

		saveRulesToStorage(scheduledRules);

		// Sync rule to backend SQLite database for background server-side execution
		if (savedRuleObject) {
			const ruleObj: ScheduledRule = savedRuleObject;
			try {
				const token = localStorage.getItem('aiffiliate_token');
				fetch(`${API_BASE}/api/workflows/rules`, {
					method: 'POST',
					headers: {
						'X-CSRF-TOKEN': getCsrfToken(),
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						...(token ? { Authorization: `Bearer ${token}` } : {})
					},
					body: JSON.stringify({
						id: ruleObj.id,
						name: ruleObj.name,
						category: ruleObj.category,
						frequency: ruleObj.frequency,
						times: ruleObj.times,
						days: ruleObj.days,
						target_page: ruleObj.targetPage,
						workflow_actions: ruleObj.workflowActions,
						action_contexts: ruleObj.actionContexts,
						general_context: ruleObj.generalContext,
						weather_context: ruleObj.weatherContext,
						occasion_context: ruleObj.occasionContext,
						tones: ruleObj.tones,
						personas: ruleObj.personas,
						custom_persona: ruleObj.customPersona,
						manual_prompt: ruleObj.manualPrompt,
						status: ruleObj.status
					})
				}).catch(e => console.warn('Backend rule sync error:', e));
			} catch (syncErr) {
				console.warn('Backend rule sync error:', syncErr);
			}
		}

		actionNotificationType = 'success';
		actionNotificationLink = null;
		setTimeout(() => actionNotification = '', 3000);
		newRuleName = '';
		editingRuleId = null;
		showTestPreviewModal = false;
		showNewScheduleModal = false;
	}

	let editingTriggerId = $state<string | null>(null);
	let newTriggerTargetPage = $state('Tech Sulit Deals');
	let newTriggerAction = $state('Auto Extract & Instant Publish');

	// Dynamic Action-Specific Configuration Fields State
	let triggerShopeeUrl = $state('https://s.shopee.ph/60QPnzrXoO');
	let triggerKeywords = $state('HM, LINK, PM, PRICE, MAGKANO, SULIT');
	let triggerReplyTemplate = $state('Hi {user_name}! 👋 Thanks for asking! Here is the deal buy link:\n🛒 {buy_link}\n\n#TechSulitDeals');
	let triggerWebhookUrl = $state('https://n8n.example.com/webhook/deal-alert');
	let triggerWebhookSecret = $state('sec_n8n_aiffiliate_2026');
	let triggerVoucherCode = $state('ANKERTECH88');
	let triggerVoucherDiscount = $state('40% OFF with voucher code ANKERTECH88');

	function openAddTriggerModal() {
		editingTriggerId = null;
		newTriggerName = '';
		newTriggerSource = 'Shopee Price Watcher';
		newTriggerCondition = 'Discount >= 40% OFF';
		newTriggerTargetPage = 'Tech Sulit Deals';
		newTriggerAction = 'Auto Extract & Instant Publish';
		triggerShopeeUrl = 'https://s.shopee.ph/60QPnzrXoO';
		triggerKeywords = 'HM, LINK, PM, PRICE, MAGKANO, SULIT';
		triggerReplyTemplate = 'Hi {user_name}! 👋 Thanks for asking! Here is the deal buy link:\n🛒 {buy_link}\n\n#TechSulitDeals';
		triggerWebhookUrl = 'https://n8n.example.com/webhook/deal-alert';
		triggerWebhookSecret = 'sec_n8n_aiffiliate_2026';
		triggerVoucherCode = 'ANKERTECH88';
		triggerVoucherDiscount = '40% OFF with voucher code ANKERTECH88';
		showNewTriggerModal = true;
	}

	function openEditTriggerModal(trigger: EventTrigger) {
		editingTriggerId = trigger.id;
		newTriggerName = trigger.name;
		newTriggerSource = trigger.eventSource;
		newTriggerCondition = trigger.condition;
		newTriggerTargetPage = trigger.targetPage;
		newTriggerAction = trigger.action;
		showNewTriggerModal = true;
	}

	function deleteTrigger(triggerId: string, triggerName: string) {
		if (confirm(`Are you sure you want to delete the event trigger "${triggerName}"?`)) {
			eventTriggers = eventTriggers.filter(t => t.id !== triggerId);
			actionNotification = `Deleted event trigger "${triggerName}"`;
			actionNotificationType = 'warning';
			actionNotificationLink = null;
			setTimeout(() => actionNotification = '', 3000);
		}
	}

	function handleAddTrigger() {
		if (!newTriggerName.trim()) {
return;
}

		if (editingTriggerId) {
			eventTriggers = eventTriggers.map(t => t.id === editingTriggerId ? {
				...t,
				name: newTriggerName.trim(),
				eventSource: newTriggerSource,
				condition: newTriggerCondition.trim(),
				targetPage: newTriggerTargetPage,
				action: newTriggerAction
			} : t);
			actionNotification = `Updated event trigger "${newTriggerName.trim()}"`;
		} else {
			const newTrig: EventTrigger = {
				id: `evt_${Date.now()}`,
				name: newTriggerName.trim(),
				eventSource: newTriggerSource,
				condition: newTriggerCondition.trim() || 'Custom Condition Met',
				targetPage: newTriggerTargetPage,
				action: newTriggerAction,
				status: 'active',
				totalFired: 0
			};
			eventTriggers = [newTrig, ...eventTriggers];
			actionNotification = `Added event trigger "${newTriggerName.trim()}"`;
		}

		actionNotificationType = 'success';
		actionNotificationLink = null;
		setTimeout(() => actionNotification = '', 3000);
		newTriggerName = '';
		editingTriggerId = null;
		showNewTriggerModal = false;
	}

	function formatScheduleText(rule: ScheduledRule) {
		if (rule.frequency === 'interval' && rule.intervalHours) {
			return `⏰ Runs Automatically Every ${rule.intervalHours} Hours`;
		} else if (rule.frequency === 'daily') {
			return `⏰ Runs ${rule.times.length} Times a Day (${rule.times.join(', ')})`;
		} else if (rule.frequency === 'weekly' && rule.days) {
			return `⏰ Runs Every ${rule.days.join(', ')} (${rule.times.join(', ')})`;
		}

		return `⏰ Runs at ${rule.times.join(', ')}`;
	}
</script>

<AppLayout title="Automated Workflows">
	<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">


<div class="flex flex-col gap-6">
	<!-- Floating Global Toast Action Notification Alert with Clickable Post Link -->
	{#if actionNotification}
		<div class="fixed top-20 right-4 sm:right-6 max-w-md w-full z-50 p-4 border rounded-2xl text-xs font-semibold flex items-center justify-between shadow-2xl backdrop-blur-2xl animate-slideDown gap-4
			{actionNotificationType === 'warning' ? 'bg-amber-950/90 border-amber-500/60 text-amber-200 shadow-amber-950/50' : 'bg-emerald-950/90 border-emerald-500/60 text-emerald-200 shadow-emerald-950/50'}">
			<div class="flex items-center gap-3 flex-wrap flex-1">
				<span class="text-base">{actionNotificationType === 'warning' ? '⚠️' : '🎉'}</span>
				<div class="flex flex-col gap-1 flex-1">
					<span class="font-bold text-white text-xs">{actionNotification}</span>
					{#if actionNotificationLink}
						<a
							href={actionNotificationLink}
							target="_blank"
							rel="noopener noreferrer"
							onclick={(e) => e.stopPropagation()}
							class="mt-1 px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white border border-emerald-400/40 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-all shadow-lg shadow-emerald-600/30 cursor-pointer active:scale-95 w-fit"
						>
							<span>🔗 View Live Post on Facebook ↗</span>
						</a>
					{/if}
				</div>
			</div>

			<button type="button" onclick={() => actionNotification = ''} class="text-gray-400 hover:text-white font-bold p-1 cursor-pointer">✕</button>
		</div>
	{/if}


	<!-- Header -->
	<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-900/60 p-6 rounded-2xl border border-gray-800/60 backdrop-blur-xl">
		<div>
			<h1 class="text-2xl font-bold text-white flex items-center gap-2">
				<span class="text-amber-400">⚡</span>
				<span>Automated & Scheduled Workflows</span>
			</h1>
			<p class="text-sm text-gray-400 mt-1">
				Dynamic time-aware AI greetings, community connection, and brand promotion workflows.
			</p>
		</div>

		<div class="flex items-center gap-2">
			{#if activeTab === 'scheduled'}
				<button
					type="button"
					onclick={openAddScheduleModal}
					class="px-4 py-2 bg-transparent hover:bg-indigo-500/10 text-indigo-300 border border-indigo-500/50 hover:border-indigo-400 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all hover:shadow-[0_0_12px_rgba(99,102,241,0.3)] active:scale-95 cursor-pointer"
				>
					<span>＋ Add Workflow Rule</span>
				</button>
			{:else if activeTab === 'event'}
				<button
					type="button"
					onclick={openAddTriggerModal}
					class="px-4 py-2 bg-transparent hover:bg-amber-500/10 text-amber-300 border border-amber-500/50 hover:border-amber-400 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all hover:shadow-[0_0_12px_rgba(245,158,11,0.3)] active:scale-95 cursor-pointer"
				>
					<span>⚡ Create Event Trigger</span>
				</button>
			{/if}
		</div>
	</div>

	<!-- Sub-Tabs Navigation -->
	<div class="flex items-center gap-2 border-b border-gray-800/60 pb-3">
		<button
			type="button"
			onclick={() => activeTab = 'scheduled'}
			class="px-4 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-2 cursor-pointer
				{activeTab === 'scheduled' ? 'bg-transparent text-indigo-300 border border-indigo-500/60 shadow-[0_0_10px_rgba(99,102,241,0.25)] font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
		>
			<span>📅 Scheduled Workflows ({scheduledRules.length})</span>
		</button>

		<button
			type="button"
			onclick={() => activeTab = 'event'}
			class="px-4 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-2 cursor-pointer
				{activeTab === 'event' ? 'bg-transparent text-amber-300 border border-amber-500/60 shadow-[0_0_10px_rgba(245,158,11,0.25)] font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
		>
			<span>⚡ Event-Based Triggers ({eventTriggers.length})</span>
		</button>

		<button
			type="button"
			onclick={() => activeTab = 'logs'}
			class="px-4 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-2 cursor-pointer
				{activeTab === 'logs' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
		>
			<span>📋 Execution Logs ({logs.length})</span>
		</button>
	</div>

	<!-- TAB 1: Scheduled Posting Rules -->
	{#if activeTab === 'scheduled'}
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
			{#each scheduledRules as rule}
				{@const isRunning = runningRuleId === rule.id}
				{@const countdown = getNextRunCountdown(rule)}
				<div class="bg-gray-900/60 border rounded-2xl p-5 flex flex-col justify-between gap-4 backdrop-blur-xl transition-all
					{isRunning ? 'border-indigo-500 shadow-[0_0_25px_rgba(99,102,241,0.3)] ring-1 ring-indigo-500' : 'border-gray-800/60 hover:border-indigo-500/40 hover:shadow-[0_0_15px_rgba(99,102,241,0.15)]'}
					{rule.status === 'disabled' && !isRunning ? 'opacity-50' : ''}">

					<div class="flex items-start justify-between">
						<div>
							<div class="flex items-center gap-2 mb-1">
								<span class="px-2 py-0.5 rounded-md text-[10px] font-semibold border inline-block
									{rule.category === 'Connection & Community' ? 'border-purple-500/40 text-purple-300' : rule.category === 'Brand Promotion' ? 'border-amber-500/40 text-amber-300' : 'border-indigo-500/40 text-indigo-300'}">
									{rule.category}
								</span>
								{#if isRunning}
									<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 animate-pulse">
										⚡ RUNNING WORKFLOW...
									</span>
								{/if}
							</div>

							<h3 class="font-bold text-white text-base">{rule.name}</h3>
							<span class="text-xs text-indigo-300 font-semibold leading-relaxed block mt-1">
								{formatScheduleText(rule)}
							</span>
						</div>

						<!-- Outer Highlight Action Icon Buttons -->
						<div class="flex items-center gap-1.5">
							<button
								type="button"
								onclick={() => openEditScheduleModal(rule)}
								title="Edit workflow rule"
								class="w-7 h-7 rounded-lg bg-gray-800/80 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-700 hover:border-gray-600 transition-all active:scale-95 flex items-center justify-center cursor-pointer"
							>
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
								</svg>
							</button>

							<button
								type="button"
								onclick={() => deleteScheduleRule(rule.id, rule.name)}
								title="Delete workflow rule"
								class="w-7 h-7 rounded-lg bg-gray-800/80 hover:bg-red-500/20 text-gray-400 hover:text-red-400 border border-gray-700 hover:border-red-500/50 transition-all active:scale-95 flex items-center justify-center cursor-pointer"
							>
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
								</svg>
							</button>

							<button
								type="button"
								onclick={() => runScheduleNow(rule)}
								disabled={isRunning}
								title={isRunning ? 'Workflow running...' : 'Run workflow now'}
								class="w-7 h-7 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 hover:text-white border border-indigo-500/40 hover:border-indigo-400 transition-all active:scale-95 flex items-center justify-center disabled:opacity-50 cursor-pointer"
							>
								{#if isRunning}
									<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
									</svg>
								{:else}
									<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
										<path d="M8 5v14l11-7z" />
									</svg>
								{/if}
							</button>

							<button
								type="button"
								onclick={() => toggleScheduleStatus(rule.id)}
								title={rule.status === 'active' ? 'Pause rule' : 'Enable rule'}
								class="w-7 h-7 rounded-lg bg-gray-800/80 transition-all active:scale-95 flex items-center justify-center cursor-pointer border
									{rule.status === 'active' ? 'border-emerald-500/50 text-emerald-400 hover:bg-emerald-500/20' : 'border-gray-700 text-gray-500 hover:text-gray-300'}"
							>
								{#if rule.status === 'active'}
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
									</svg>
								{:else}
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
								{/if}
							</button>
						</div>
					</div>

					<!-- Times Pills Display -->
					<div class="flex flex-wrap gap-1.5">
						{#each rule.times as timeSlot}
							<span class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-semibold bg-transparent text-indigo-300 border border-indigo-500/30">
								{timeSlot}
							</span>
						{/each}
					</div>

					<!-- Live Animated Action Workflow Step Progression -->
					<div class="flex flex-col gap-1.5 bg-gray-950/70 p-3 rounded-xl border border-gray-800/50">
						<div class="flex items-center justify-between">
							<span class="text-[11px] font-semibold text-indigo-400 uppercase tracking-wider">Preset Actions Workflow</span>
							{#if isRunning}
								<span class="text-[10px] text-indigo-300 font-mono animate-pulse">Step {activeStepIndex + 1} of {rule.workflowActions.length}</span>
							{/if}
						</div>

						<div class="flex flex-col gap-1">
							{#each rule.workflowActions as actionName, idx}
								{@const isCurrentStep = isRunning && activeStepIndex === idx}
								{@const isCompletedStep = isRunning && activeStepIndex > idx}
								{@const customCtx = rule.actionContexts?.[actionName]}
								<div class="flex flex-col gap-1 p-1.5 rounded-lg border transition-all
									{isCurrentStep ? 'bg-indigo-500/20 border-indigo-500/60 text-white font-bold shadow-[0_0_10px_rgba(99,102,241,0.3)] animate-pulse' : isCompletedStep ? 'bg-gray-950/40 border-emerald-500/30 text-emerald-300 font-semibold' : 'bg-gray-950/40 border-gray-800/60 text-gray-300'}">
									<div class="flex items-start justify-between gap-2 text-xs font-mono min-w-0 max-w-full">
										<div class="flex items-start gap-2 min-w-0 flex-1">
											<span class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center font-bold border transition-all shrink-0 mt-0.5
												{isCurrentStep ? 'bg-indigo-500 border-indigo-400 text-white animate-spin' : isCompletedStep ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-300' : 'bg-indigo-500/20 border-indigo-500/40 text-indigo-300'}">
												{isCompletedStep ? '✓' : isCurrentStep ? '🌀' : idx + 1}
											</span>
											<span class="break-words whitespace-normal leading-snug flex-1 min-w-0">{actionName}</span>
										</div>
									</div>

									{#if customCtx}
										<div class="text-[10px] text-indigo-300 font-sans italic pl-6 break-words whitespace-normal leading-snug">
											💬 Context: "{customCtx}"
										</div>
									{/if}
								</div>
							{/each}
						</div>
					</div>

					<!-- Next Run Countdown Card Section -->
					<div class="flex flex-col gap-2 p-3.5 bg-gradient-to-r from-indigo-950/60 to-purple-950/40 rounded-xl border border-indigo-500/40 backdrop-blur-md shadow-inner">
						<div class="flex items-center justify-between">
							<span class="text-[10px] font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-1.5 font-mono">
								<span class="animate-pulse">⏳</span>
								<span>Next Scheduled Run</span>
							</span>
							<span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md border
								{rule.status === 'active' ? 'bg-purple-500/20 text-purple-300 border-purple-500/40' : 'bg-gray-800 text-gray-400 border-gray-700'}">
								{rule.status === 'active' ? 'ACTIVE' : 'PAUSED'}
							</span>
						</div>

						<div class="text-sm font-bold font-mono text-white tracking-wide flex items-center gap-2">
							<span class="text-indigo-400">⏰</span>
							<span>{countdown.text}</span>
						</div>

						<div class="text-[11px] text-gray-400 font-sans flex items-center justify-between">
							<span>{countdown.detail}</span>
							<button
								type="button"
								onclick={() => activeTab = 'logs'}
								class="text-[10px] font-semibold text-indigo-400 hover:text-indigo-200 underline cursor-pointer flex items-center gap-1"
							>
								<span>View Logs ↗</span>
							</button>
						</div>
					</div>

					<!-- Footer: See Execution Logs button -->
					<button
						type="button"
						onclick={() => navigateToFilteredLogs(rule.name)}
						class="w-full py-2.5 bg-transparent hover:bg-emerald-500/10 text-emerald-300 border border-emerald-500/40 hover:border-emerald-400 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all hover:shadow-[0_0_12px_rgba(16,185,129,0.3)] active:scale-95 cursor-pointer"
					>
						<span>📋 See Execution Logs</span>
					</button>
				</div>
			{/each}
		</div>
	{/if}

	<!-- TAB 2: Event-Based Triggers -->
	{#if activeTab === 'event'}
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
			{#each eventTriggers as trigger}
				{@const isTriggerRunning = runningTriggerId === trigger.id}
				<div class="bg-gray-900/60 border rounded-2xl p-5 flex flex-col justify-between gap-4 backdrop-blur-xl transition-all
					{isTriggerRunning ? 'border-amber-500 shadow-[0_0_25px_rgba(245,158,11,0.3)] ring-1 ring-amber-500' : 'border-gray-800/60 hover:border-amber-500/40 hover:shadow-[0_0_15px_rgba(245,158,11,0.15)]'}
					{trigger.status === 'disabled' && !isTriggerRunning ? 'opacity-50' : ''}">
					<div class="flex items-start justify-between">
						<div>
							<h3 class="font-bold text-white text-base">{trigger.name}</h3>
							<span class="text-xs text-amber-400 font-medium">{trigger.eventSource}</span>
						</div>

						<div class="flex items-center gap-1.5">
							<button
								type="button"
								onclick={() => openEditTriggerModal(trigger)}
								title="Edit event trigger"
								class="w-7 h-7 rounded-lg bg-gray-800/80 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-700 hover:border-gray-600 transition-all active:scale-95 flex items-center justify-center cursor-pointer"
							>
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
								</svg>
							</button>

							<button
								type="button"
								onclick={() => deleteTrigger(trigger.id, trigger.name)}
								title="Delete event trigger"
								class="w-7 h-7 rounded-lg bg-gray-800/80 hover:bg-red-500/20 text-gray-400 hover:text-red-400 border border-gray-700 hover:border-red-500/50 transition-all active:scale-95 flex items-center justify-center cursor-pointer"
							>
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
								</svg>
							</button>

							<button
								type="button"
								onclick={() => runTriggerNow(trigger)}
								disabled={isTriggerRunning}
								title="Run trigger now (Manual Override)"
								class="w-7 h-7 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 hover:text-white border border-amber-500/40 hover:border-amber-400 transition-all active:scale-95 flex items-center justify-center disabled:opacity-50 cursor-pointer"
							>
								{#if isTriggerRunning}
									<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
									</svg>
								{:else}
									<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
										<path d="M8 5v14l11-7z" />
									</svg>
								{/if}
							</button>

							<button
								type="button"
								onclick={() => toggleTriggerStatus(trigger.id)}
								title={trigger.status === 'active' ? 'Pause trigger' : 'Enable trigger'}
								class="w-7 h-7 rounded-lg bg-gray-800/80 transition-all active:scale-95 flex items-center justify-center cursor-pointer border
									{trigger.status === 'active' ? 'border-amber-500/50 text-amber-400 hover:bg-amber-500/20' : 'border-gray-700 text-gray-500 hover:text-gray-300'}"
							>
								{#if trigger.status === 'active'}
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
									</svg>
								{:else}
									<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
								{/if}
							</button>
						</div>
					</div>

					<div class="flex flex-col gap-1.5 text-xs text-gray-400 bg-gray-950/60 p-3 rounded-xl border border-gray-800/40">
						<div>Condition: <span class="text-gray-200 font-semibold">{trigger.condition}</span></div>
						<div>Action: <span class="text-amber-300 font-semibold">{trigger.action}</span></div>
						<div class="flex justify-between pt-1 border-t border-gray-800/40">
							<span>Times Fired:</span>
							<span class="text-emerald-400 font-bold">{trigger.totalFired} times</span>
						</div>
					</div>

					<!-- Footer: See Execution Logs button -->
					<button
						type="button"
						onclick={() => navigateToFilteredLogs(trigger.name)}
						class="w-full py-2.5 bg-transparent hover:bg-amber-500/10 text-amber-300 border border-amber-500/40 hover:border-amber-400 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all hover:shadow-[0_0_12px_rgba(245,158,11,0.3)] active:scale-95 cursor-pointer"
					>
						<span>📋 See Execution Logs</span>
					</button>
				</div>
			{/each}
		</div>
	{/if}

	<!-- TAB 3: Execution Logs -->
	{#if activeTab === 'logs'}
		<div class="bg-gray-900/60 border border-gray-800/60 rounded-2xl p-6 backdrop-blur-xl flex flex-col gap-5">
			<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
				<div>
					<h2 class="font-bold text-white text-lg flex items-center gap-2">
						<span>📋 Execution Logs & Activity History</span>
						<span class="px-2 py-0.5 rounded-full text-xs font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
							{filteredLogs.length} / {logs.length}
						</span>
					</h2>
					<p class="text-xs text-gray-400 mt-0.5">Filter logs by rule name, trigger event, execution type, or status.</p>
				</div>

				{#if logRuleFilter}
					<div class="flex items-center gap-2 bg-indigo-500/15 border border-indigo-500/40 px-3 py-1.5 rounded-xl">
						<span class="text-xs text-indigo-300 font-semibold">Filtered for: "{logRuleFilter}"</span>
						<button
							type="button"
							onclick={clearLogRuleFilter}
							class="text-indigo-400 hover:text-white text-xs font-bold px-1 cursor-pointer"
							title="Clear Rule Filter"
						>
							✕
						</button>
					</div>
				{/if}
			</div>

			<!-- Logs Search & Filter Control Bar -->
			<div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-gray-950/60 p-4 rounded-xl border border-gray-800/60">
				<div class="flex-1 relative">
					<input
						type="text"
						bind:value={logSearchQuery}
						placeholder="Search logs by rule name, title, or target page..."
						class="w-full bg-gray-900 border border-gray-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-500 outline-none focus:border-indigo-500/60 transition-all"
					/>
					{#if logSearchQuery}
						<button
							type="button"
							onclick={() => logSearchQuery = ''}
							class="absolute right-3 top-2 text-gray-500 hover:text-white text-xs cursor-pointer"
						>
							✕
						</button>
					{/if}
				</div>

				<div class="flex items-center gap-1 overflow-x-auto pb-1 lg:pb-0">
					<span class="text-[11px] text-gray-500 font-semibold mr-1">Type:</span>
					<button
						type="button"
						onclick={() => logTypeFilter = 'all'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logTypeFilter === 'all' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						All
					</button>
					<button
						type="button"
						onclick={() => logTypeFilter = 'scheduled'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logTypeFilter === 'scheduled' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						Scheduled
					</button>
					<button
						type="button"
						onclick={() => logTypeFilter = 'event'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logTypeFilter === 'event' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						Event
					</button>
					<button
						type="button"
						onclick={() => logTypeFilter = 'manual'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logTypeFilter === 'manual' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						Manual
					</button>
				</div>

				<div class="flex items-center gap-1 overflow-x-auto pb-1 lg:pb-0">
					<span class="text-[11px] text-gray-500 font-semibold mr-1">Status:</span>
					<button
						type="button"
						onclick={() => logStatusFilter = 'all'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logStatusFilter === 'all' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						All
					</button>
					<button
						type="button"
						onclick={() => logStatusFilter = 'SUCCESS'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logStatusFilter === 'SUCCESS' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						Success
					</button>
					<button
						type="button"
						onclick={() => logStatusFilter = 'FAILED'}
						class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all cursor-pointer
							{logStatusFilter === 'FAILED' ? 'bg-red-500/20 text-red-300 border border-red-500/40 font-semibold' : 'text-gray-400 hover:text-white border border-transparent'}"
					>
						Failed
					</button>
				</div>
			</div>

			<!-- Logs Table -->
			<div class="overflow-x-auto">
				{#if filteredLogs.length === 0}
					<div class="p-8 text-center bg-gray-950/40 rounded-xl border border-gray-800/40 flex flex-col items-center gap-2">
						<span class="text-2xl">🔍</span>
						<span class="text-sm font-semibold text-gray-300">No execution logs match your filter criteria.</span>
						<button
							type="button"
							onclick={() => {
 logSearchQuery = ''; logTypeFilter = 'all'; logStatusFilter = 'all'; logRuleFilter = null; 
}}
							class="text-xs text-indigo-400 hover:underline mt-1 cursor-pointer"
						>
							Reset all filters
						</button>
					</div>
				{:else}
					<table class="w-full text-left text-xs font-mono">
						<thead>
							<tr class="border-b border-gray-800/80 text-gray-400">
								<th class="py-3 px-2">Timestamp</th>
								<th class="py-3 px-2">Type</th>
								<th class="py-3 px-2">Rule / Trigger</th>
								<th class="py-3 px-2">Post Title</th>
								<th class="py-3 px-2">Target Page</th>
								<th class="py-3 px-2">Status</th>
								<th class="py-3 px-2">Output Post Link</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-gray-800/40 text-gray-300">
							{#each filteredLogs as log}
								<tr class="hover:bg-white/5 transition-colors">
									<td class="py-3 px-2 text-gray-400">{log.timestamp}</td>
									<td class="py-3 px-2">
										<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border
											{log.type === 'scheduled' ? 'bg-transparent border-indigo-500/40 text-indigo-300' : log.type === 'manual' ? 'bg-transparent border-purple-500/40 text-purple-300' : 'bg-transparent border-amber-500/40 text-amber-300'}">
											{log.type.toUpperCase()}
										</span>
									</td>
									<td class="py-3 px-2 font-semibold text-white">{log.ruleName}</td>
									<td class="py-3 px-2 text-gray-300 truncate max-w-xs">{log.dealTitle}</td>
									<td class="py-3 px-2 text-indigo-400">{log.targetPage}</td>
									<td class="py-3 px-2">
										<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border border-emerald-500/40 text-emerald-400">
											{log.status}
										</span>
									</td>
									<td class="py-3 px-2">
										{#if log.postUrl}
											<a
												href={log.postUrl}
												target="_blank"
												rel="noopener noreferrer"
												class="px-2.5 py-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 transition-all underline cursor-pointer"
											>
												<span>🔗 View Post ↗</span>
											</a>
										{:else}
											<span class="text-gray-600 font-sans text-[11px]">—</span>
										{/if}
									</td>
								</tr>
							{/each}
						</tbody>
					</table>
				{/if}
			</div>
		</div>
	{/if}
</div>

<!-- Modal: Generated Facebook Post Preview -->
{#if selectedPreviewPost}
	<div
		class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-black/85 backdrop-blur-md animate-fadeIn"
		onclick={closePreviewModal}
		role="dialog"
		aria-modal="true"
	>
		<div
			class="bg-gray-950 border border-indigo-500/40 rounded-2xl p-6 max-w-2xl w-full flex flex-col gap-4 shadow-2xl relative"
			onclick={(e) => e.stopPropagation()}
		>
			<div class="flex items-center justify-between border-b border-gray-800 pb-3">
				<div class="flex items-center gap-2">
					<div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs">
						f
					</div>
					<div>
						<h3 class="font-bold text-white text-sm">{selectedPreviewPost.targetPage}</h3>
						<span class="text-[10px] text-gray-400">Published • {selectedPreviewPost.postedAt}</span>
					</div>
				</div>

				<button
					type="button"
					onclick={closePreviewModal}
					class="text-gray-400 hover:text-white p-1 text-lg font-bold cursor-pointer"
				>
					✕
				</button>
			</div>

			<!-- Post Content -->
			<div class="flex flex-col gap-3">
				<h4 class="font-bold text-indigo-300 text-sm">{selectedPreviewPost.title}</h4>
				<p class="text-xs text-gray-200 whitespace-pre-wrap leading-relaxed bg-gray-900/80 p-3.5 rounded-xl border border-gray-800/60 font-sans select-text">
					{selectedPreviewPost.caption}
				</p>

				{#if selectedPreviewPost.image}
					<div class="rounded-xl overflow-hidden border border-gray-800/60 max-h-72">
						<img src={selectedPreviewPost.image} alt="Generated Post Media" class="w-full h-full object-cover" />
					</div>
				{/if}
			</div>

			<div class="flex items-center justify-between border-t border-gray-800 pt-3 mt-2 text-xs">
				<span class="text-emerald-400 font-semibold flex items-center gap-1">
					<span>✓ Dynamic Time-Aware AI Post Generated</span>
				</span>

				{#if selectedPreviewPost.postUrl}
					<a
						href={selectedPreviewPost.postUrl}
						target="_blank"
						rel="noopener noreferrer"
						onclick={(e) => e.stopPropagation()}
						class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 rounded-xl font-semibold flex items-center gap-1 text-xs transition-all underline cursor-pointer z-10"
					>
						<span>🔗 View on Facebook ↗</span>
					</a>
				{/if}

				<button
					type="button"
					onclick={closePreviewModal}
					class="px-4 py-2 bg-transparent hover:bg-gray-800 text-gray-300 rounded-xl font-medium border border-gray-700 cursor-pointer"
				>
					Close Preview
				</button>
			</div>
		</div>
	</div>
{/if}

<!-- Modal: Test Content Generation Preview Before Saving Rule -->
{#if showTestPreviewModal}
	<div
		class="fixed inset-0 z-[110] flex items-center justify-center p-3 sm:p-6 bg-black/85 backdrop-blur-md animate-fadeIn"
		onclick={() => showTestPreviewModal = false}
		role="dialog"
		aria-modal="true"
	>
		<div
			class="bg-gray-950 border border-emerald-500/40 rounded-2xl p-6 max-w-2xl w-full flex flex-col gap-4 shadow-2xl relative"
			onclick={(e) => e.stopPropagation()}
		>
			<div class="flex items-center justify-between border-b border-gray-800 pb-3">
				<div class="flex items-center gap-2">
					<div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs">
						✨
					</div>
					<div>
						<h3 class="font-bold text-white text-sm">Generated Content Preview ({newRulePage})</h3>
						<span class="text-[10px] text-emerald-400 font-mono">Test Run Before Saving Rule</span>
					</div>
				</div>

				<button
					type="button"
					onclick={() => showTestPreviewModal = false}
					class="text-gray-400 hover:text-white p-1 text-lg font-bold cursor-pointer"
				>
					✕
				</button>
			</div>

			<!-- Loading State or Post Content -->
			{#if isTestingWorkflow}
				<div class="flex flex-col items-center justify-center p-12 gap-3">
					<div class="w-10 h-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
					<span class="text-xs text-indigo-300 font-semibold animate-pulse">✨ Generating AI content preview...</span>
				</div>
			{:else if testPreviewResult}
				<div class="flex flex-col gap-3">
					<h4 class="font-bold text-indigo-300 text-sm">{testPreviewResult.title}</h4>
					<p class="text-xs text-gray-200 whitespace-pre-wrap leading-relaxed bg-gray-900/90 p-4 rounded-xl border border-gray-800/80 font-sans select-text max-h-64 overflow-y-auto">
						{testPreviewResult.caption}
					</p>

					{#if testPreviewResult.image}
						<div class="rounded-xl overflow-hidden border border-gray-800/60 max-h-60">
							<img src={testPreviewResult.image} alt="Generated Test Media" class="w-full h-full object-cover" />
						</div>
					{/if}
				</div>
			{/if}

			<div class="flex items-center justify-between border-t border-gray-800 pt-3 mt-2 text-xs">
				<button
					type="button"
					onclick={() => showTestPreviewModal = false}
					class="px-4 py-2 bg-transparent hover:bg-gray-800 text-gray-300 rounded-xl font-medium border border-gray-700 cursor-pointer"
				>
					← Adjust Rule & Actions
				</button>

				<div class="flex items-center gap-2">
					<button
						type="button"
						onclick={testGeneratePreview}
						disabled={isTestingWorkflow}
						class="px-3.5 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/50 rounded-xl font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"
					>
						{#if isTestingWorkflow}
							<span class="inline-block animate-spin">🌀</span>
						{:else}
							<span>🔄 Regenerate Content</span>
						{/if}
					</button>

					<button
						type="button"
						onclick={() => {
 showTestPreviewModal = false; handleSaveSchedule(); 
}}
						class="px-5 py-2 bg-transparent hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/60 hover:border-emerald-400 font-semibold rounded-xl text-xs hover:shadow-[0_0_12px_rgba(16,185,129,0.4)] transition-all cursor-pointer"
					>
						Save & Activate Rule 🚀
					</button>
				</div>
			</div>
		</div>
	</div>
{/if}

<!-- Modal: New or Edit Schedule Rule (Wider max-w-3xl Container with Intuitive 3-Step Wizard & Action Docs) -->
{#if showNewScheduleModal}
	<div
		class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fadeIn"
		onclick={() => showNewScheduleModal = false}
		role="dialog"
		aria-modal="true"
	>
		<div
			class="bg-gray-950 border border-indigo-500/30 rounded-2xl p-6 max-w-3xl w-full flex flex-col gap-5 max-h-[92vh] overflow-y-auto shadow-2xl relative"
			onclick={(e) => e.stopPropagation()}
		>
			<!-- Modal Header & Wizard Step Progress Indicator -->
			<div class="flex flex-col gap-3 border-b border-gray-800 pb-3.5">
				<div class="flex items-center justify-between">
					<div>
						<h3 class="font-bold text-white text-lg">
							{editingRuleId ? 'Edit Scheduled Workflow Rule' : 'Add Scheduled Workflow Rule'}
						</h3>
						<p class="text-xs text-gray-400 mt-0.5">
							Step {wizardStep} of 3: {wizardStep === 1 ? 'Choose Starting Template' : wizardStep === 2 ? 'Configure Schedule & Page' : 'Content Generation Parameters & Preview'}
						</p>
					</div>

					<button
						type="button"
						onclick={() => showNewScheduleModal = false}
						class="text-gray-400 hover:text-white p-1 text-lg font-bold cursor-pointer"
					>
						✕
					</button>
				</div>

				<!-- Step Navigation Bar -->
				<div class="grid grid-cols-3 gap-2">
					<button
						type="button"
						onclick={() => wizardStep = 1}
						class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer text-left
							{wizardStep === 1 ? 'bg-indigo-500/20 border-indigo-500/60 text-indigo-300 shadow-[0_0_10px_rgba(99,102,241,0.2)]' : wizardStep > 1 ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-gray-900 border-gray-800 text-gray-400'}"
					>
						<span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border shrink-0
							{wizardStep === 1 ? 'bg-indigo-500 text-white border-indigo-400' : wizardStep > 1 ? 'bg-emerald-500/30 text-emerald-300 border-emerald-500/50' : 'bg-gray-800 border-gray-700'}">
							{wizardStep > 1 ? '✓' : '1'}
						</span>
						<span class="truncate">1. Template</span>
					</button>

					<button
						type="button"
						onclick={() => wizardStep = 2}
						class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer text-left
							{wizardStep === 2 ? 'bg-indigo-500/20 border-indigo-500/60 text-indigo-300 shadow-[0_0_10px_rgba(99,102,241,0.2)]' : wizardStep > 2 ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300' : 'bg-gray-900 border-gray-800 text-gray-400'}"
					>
						<span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border shrink-0
							{wizardStep === 2 ? 'bg-indigo-500 text-white border-indigo-400' : wizardStep > 2 ? 'bg-emerald-500/30 text-emerald-300 border-emerald-500/50' : 'bg-gray-800 border-gray-700'}">
							{wizardStep > 2 ? '✓' : '2'}
						</span>
						<span class="truncate">2. Schedule</span>
					</button>

					<button
						type="button"
						onclick={() => wizardStep = 3}
						class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer text-left
							{wizardStep === 3 ? 'bg-indigo-500/20 border-indigo-500/60 text-indigo-300 shadow-[0_0_10px_rgba(99,102,241,0.2)]' : 'bg-gray-900 border-gray-800 text-gray-400'}"
					>
						<span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border shrink-0
							{wizardStep === 3 ? 'bg-indigo-500 text-white border-indigo-400' : 'bg-gray-800 border-gray-700'}">
							3
						</span>
						<span class="truncate">3. Content Parameters</span>
					</button>
				</div>
			</div>

			<!-- WIZARD STEP 1: Choose Starting Template -->
			{#if wizardStep === 1}
				<div class="flex flex-col gap-4">
					<p class="text-xs text-gray-400">Select a pre-configured template to auto-fill your workflow, or start from scratch:</p>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
						{#each rulePresets as preset}
							<button
								type="button"
								onclick={() => {
 applyPreset(preset); wizardStep = 2; 
}}
								class="p-4 bg-gray-900/80 border border-indigo-500/30 hover:border-indigo-400 hover:shadow-[0_0_15px_rgba(99,102,241,0.25)] rounded-2xl text-left transition-all flex flex-col justify-between gap-3 group cursor-pointer"
							>
								<div class="flex items-start gap-3">
									<span class="text-2xl p-2 bg-indigo-500/10 rounded-xl border border-indigo-500/30">{preset.icon}</span>
									<div class="flex flex-col">
										<h4 class="font-bold text-white text-sm group-hover:text-indigo-300 transition-colors">{preset.name}</h4>
										<span class="text-[10px] text-indigo-400 font-semibold mt-0.5">{preset.category}</span>
										<p class="text-xs text-gray-400 mt-1 leading-relaxed">{preset.description}</p>
									</div>
								</div>

								<div class="flex items-center justify-between pt-2 border-t border-gray-800/60 text-[11px]">
									<span class="text-gray-400 font-mono">⏰ {preset.times.join(', ')}</span>
									<span class="text-indigo-300 font-semibold group-hover:underline">Use Template ➔</span>
								</div>
							</button>
						{/each}

						<!-- Custom Scratch Card -->
						<button
							type="button"
							onclick={() => wizardStep = 2}
							class="p-4 bg-gray-900/40 border border-purple-500/30 hover:border-purple-400 hover:shadow-[0_0_15px_rgba(168,85,247,0.25)] rounded-2xl text-left transition-all flex flex-col justify-between gap-3 group cursor-pointer"
						>
							<div class="flex items-start gap-3">
								<span class="text-2xl p-2 bg-purple-500/10 rounded-xl border border-purple-500/30">🛠️</span>
								<div class="flex flex-col">
									<h4 class="font-bold text-white text-sm group-hover:text-purple-300 transition-colors">Build Custom Rule from Scratch</h4>
									<span class="text-[10px] text-purple-400 font-semibold mt-0.5">Custom Setup</span>
									<p class="text-xs text-gray-400 mt-1 leading-relaxed">Configure custom rule names, frequencies, time slots, and action step combinations manually.</p>
								</div>
							</div>

							<div class="flex items-center justify-end pt-2 border-t border-gray-800/60 text-[11px]">
								<span class="text-purple-300 font-semibold group-hover:underline">Start Blank Setup ➔</span>
							</div>
						</button>
					</div>
				</div>
			{/if}

			<!-- WIZARD STEP 2: Configure Rule Name, Frequency & Times -->
			{#if wizardStep === 2}
				<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
					<!-- Left Column: Rule Details -->
					<div class="flex flex-col gap-4">
						<div class="flex flex-col gap-1.5">
							<label class="text-xs text-gray-400 font-medium" for="rule_name">Rule Name</label>
							<input
								id="rule_name"
								type="text"
								bind:value={newRuleName}
								placeholder="Dynamic Time-Aware AI Greeting & Interactive Fan Post"
								class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-indigo-500"
							/>
						</div>

						<!-- Category Selector -->
						<div class="flex flex-col gap-1.5">
							<span class="text-xs text-gray-400 font-medium">Workflow Category</span>
							<div class="relative">
								<button
									type="button"
									onclick={() => categoryDropdownOpen = !categoryDropdownOpen}
									class="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-indigo-500 flex items-center justify-between text-left cursor-pointer"
								>
									<span>{categoryOptions.find(o => o.value === newRuleCategory)?.label || newRuleCategory}</span>
									<span class="text-[10px] text-gray-400">{categoryDropdownOpen ? '▲' : '▼'}</span>
								</button>

								{#if categoryDropdownOpen}
									<div class="absolute z-50 left-0 right-0 mt-1 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col divide-y divide-gray-800/60">
										{#each categoryOptions as opt}
											<button
												type="button"
												onclick={() => {
 newRuleCategory = opt.value as any; categoryDropdownOpen = false; 
}}
												class="px-3 py-2.5 text-xs text-left transition-colors flex items-center justify-between cursor-pointer
													{newRuleCategory === opt.value ? 'bg-indigo-500/20 text-indigo-300 font-semibold' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white'}"
											>
												<span>{opt.label}</span>
												{#if newRuleCategory === opt.value}
													<span class="text-indigo-400 font-bold">✓</span>
												{/if}
											</button>
										{/each}
									</div>
								{/if}
							</div>
						</div>

						<!-- Frequency Selector -->
						<div class="flex flex-col gap-1.5">
							<span class="text-xs text-gray-400 font-medium">Posting Frequency</span>
							<div class="relative">
								<button
									type="button"
									onclick={() => frequencyDropdownOpen = !frequencyDropdownOpen}
									class="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-indigo-500 flex items-center justify-between text-left cursor-pointer"
								>
									<span>{frequencyOptions.find(o => o.value === newRuleFrequency)?.label || newRuleFrequency}</span>
									<span class="text-[10px] text-gray-400">{frequencyDropdownOpen ? '▲' : '▼'}</span>
								</button>

								{#if frequencyDropdownOpen}
									<div class="absolute z-50 left-0 right-0 mt-1 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col divide-y divide-gray-800/60">
										{#each frequencyOptions as opt}
											<button
												type="button"
												onclick={() => {
 newRuleFrequency = opt.value as any; frequencyDropdownOpen = false; 
}}
												class="px-3 py-2.5 text-xs text-left transition-colors flex items-center justify-between cursor-pointer
													{newRuleFrequency === opt.value ? 'bg-indigo-500/20 text-indigo-300 font-semibold' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white'}"
											>
												<span>{opt.label}</span>
												{#if newRuleFrequency === opt.value}
													<span class="text-indigo-400 font-bold">✓</span>
												{/if}
											</button>
										{/each}
									</div>
								{/if}
							</div>
						</div>

						<!-- Target Connected Social Media Account -->
						<div class="flex flex-col gap-1.5">
							<div class="flex items-center justify-between">
								<span class="text-xs text-gray-400 font-medium">Target Connected Social Media Account</span>
								<a href="/settings" class="text-[10px] text-indigo-300 hover:text-indigo-200 hover:underline flex items-center gap-1 font-semibold">
									<span>⚙️ Manage Accounts in Settings</span>
								</a>
							</div>
							<div class="relative">
								<button
									type="button"
									onclick={() => pageDropdownOpen = !pageDropdownOpen}
									class="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-indigo-500 flex items-center justify-between text-left cursor-pointer"
								>
									<span>{pageOptions.find(o => o.value === newRulePage)?.label || newRulePage}</span>
									<span class="text-[10px] text-gray-400">{pageDropdownOpen ? '▲' : '▼'}</span>
								</button>

								{#if pageDropdownOpen}
									<div class="absolute z-50 left-0 right-0 mt-1 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col divide-y divide-gray-800/60">
										{#each pageOptions as opt}
											<button
												type="button"
												onclick={() => {
 newRulePage = opt.value; pageDropdownOpen = false; 
}}
												class="px-3 py-2.5 text-xs text-left transition-colors flex items-center justify-between cursor-pointer
													{newRulePage === opt.value ? 'bg-indigo-500/20 text-indigo-300 font-semibold' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white'}"
											>
												<span>{opt.label}</span>
												{#if newRulePage === opt.value}
													<span class="text-indigo-400 font-bold">✓</span>
												{/if}
											</button>
										{/each}
									</div>
								{/if}
							</div>
						</div>
					</div>

					<!-- Right Column: Scheduled Posting Times -->
					<div class="flex flex-col gap-4">
						{#if newRuleFrequency === 'daily' || newRuleFrequency === 'weekly'}
							<div class="flex flex-col gap-2">
								<span class="text-xs text-gray-400 font-medium">Scheduled Posting Times</span>

								<div class="flex flex-wrap items-center gap-2 p-3 bg-gray-900 border border-gray-800 rounded-xl min-h-[64px]">
									{#if manualTimeSlots.length === 0}
										<span class="text-xs text-gray-500 italic">No posting times set. Select a time below to add.</span>
									{:else}
										{#each manualTimeSlots as timeSlot, idx}
											<div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-transparent text-indigo-300 border border-indigo-500/40 shadow-sm">
												<span>{timeSlot}</span>
												<button
													type="button"
													onclick={() => removeTimeSlot(idx)}
													class="text-indigo-400 hover:text-red-400 transition-colors ml-1 font-bold cursor-pointer"
													title="Remove Time"
												>
													✕
												</button>
											</div>
										{/each}
									{/if}
								</div>

								<!-- HTML5 Native Time Picker Input + Add Button -->
								<div class="flex items-center gap-2 mt-1">
									<input
										type="time"
										bind:value={nativeTimeInput}
										class="flex-1 bg-gray-900 border border-gray-800 rounded-xl p-2.5 text-xs font-mono text-white outline-none focus:border-indigo-500"
									/>
									<button
										type="button"
										onclick={addTimeFromSelector}
										class="px-4 py-2.5 bg-transparent hover:bg-indigo-500/10 text-indigo-300 border border-indigo-500/50 hover:border-indigo-400 rounded-xl text-xs font-semibold flex items-center gap-1 transition-all active:scale-95 cursor-pointer"
									>
										<span>＋ Add Time</span>
									</button>
								</div>
							</div>
						{/if}
					</div>
				</div>
			{/if}

			<!-- WIZARD STEP 3: 3 Sub-Stepped Setup (3.1 Tone & Persona ➔ 3.2 Action Pipeline ➔ 3.3 Summary & Preview) -->
			{#if wizardStep === 3}
				<div class="flex flex-col gap-4">
					<!-- Sub-Step Navigation Header Bar -->
					<div class="grid grid-cols-3 gap-2 bg-gray-900/90 p-1.5 rounded-2xl border border-gray-800">
						<button
							type="button"
							onclick={() => subStep3 = 1}
							class="py-2 px-3 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer
								{subStep3 === 1 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/60'}"
						>
							<span>3.1 🎨 Tone & Persona</span>
						</button>

						<button
							type="button"
							onclick={() => subStep3 = 2}
							class="py-2 px-3 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer
								{subStep3 === 2 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/60'}"
						>
							<span>3.2 ⚡ Content Parameters ({selectedWorkflowActions.length})</span>
						</button>

						<button
							type="button"
							onclick={() => subStep3 = 3}
							class="py-2 px-3 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer
								{subStep3 === 3 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/60'}"
						>
							<span>3.3 👁️ Summary & Preview</span>
						</button>
					</div>

					<!-- SUB-STEP 3.1: Tone, Persona & Custom Voice -->
					{#if subStep3 === 1}
						<div class="flex flex-col gap-4 bg-gray-900/40 p-4 rounded-2xl border border-gray-800 animate-fadeIn">
							<!-- General Post Topic / Niche Context (Optional) -->
							<div class="flex flex-col gap-1.5 p-3 bg-indigo-950/20 border border-indigo-500/30 rounded-xl">
								<div class="flex items-center justify-between text-xs">
									<span class="text-indigo-300 font-bold flex items-center gap-1">
										<span>📌 General Post Topic / Niche Context</span>
										<span class="text-gray-400 font-normal text-[10px]">(Optional)</span>
									</span>
									<span class="text-[10px] text-gray-500 font-mono">Global post theme</span>
								</div>
								<input
									type="text"
									bind:value={generalPostContext}
									placeholder="e.g. Weekend WFH coffee setup & mechanical keyboard deal recommendations"
									class="bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white placeholder-gray-600 outline-none focus:border-indigo-500 transition-all font-sans"
								/>
							</div>

							<!-- Weather & Occasion Context Awareness Cards -->
							<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
								<!-- Weather Context -->
								<div class="flex flex-col gap-1.5 p-3 bg-blue-950/20 border border-blue-500/30 rounded-xl">
									<div class="flex items-center justify-between text-xs">
										<span class="text-blue-300 font-bold flex items-center gap-1">
											<span>🌧️ Weather Awareness Context</span>
											<span class="text-gray-400 font-normal text-[10px]">(Optional)</span>
										</span>
									</div>
									<input
										type="text"
										bind:value={weatherContext}
										placeholder="e.g. Cozy rainy afternoon setup check-in"
										class="bg-gray-950 border border-gray-800 rounded-xl p-2 text-xs text-white placeholder-gray-600 outline-none focus:border-blue-500 transition-all font-sans"
									/>
								</div>

								<!-- Occasion Context -->
								<div class="flex flex-col gap-1.5 p-3 bg-amber-950/20 border border-amber-500/30 rounded-xl">
									<div class="flex items-center justify-between text-xs">
										<span class="text-amber-300 font-bold flex items-center gap-1">
											<span>🎉 Occasion / Holiday Context</span>
											<span class="text-gray-400 font-normal text-[10px]">(Optional)</span>
										</span>
									</div>
									<input
										type="text"
										bind:value={occasionContext}
										placeholder="e.g. 8.8 Payday Sale or Weekend Gaming Lounge"
										class="bg-gray-950 border border-gray-800 rounded-xl p-2 text-xs text-white placeholder-gray-600 outline-none focus:border-amber-500 transition-all font-sans"
									/>
								</div>
							</div>

							<!-- Tone Multi-Select -->
							<div class="flex flex-col gap-2 border-t border-gray-800/60 pt-3">
								<div class="flex items-center justify-between">
									<span class="text-xs font-bold text-indigo-300 flex items-center gap-1">
										<span>🎨 Copywriting Tone</span>
										<span class="text-[10px] font-normal text-gray-400">(Optional)</span>
									</span>
									<span class="text-[10px] text-gray-500 font-mono">{selectedTones.length} Selected</span>
								</div>
								<div class="flex flex-wrap gap-2">
									{#each availableTones as t}
										{@const isSelectedTone = selectedTones.includes(t.id)}
										<button
											type="button"
											onclick={() => toggleTone(t.id)}
											title={t.description}
											class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all cursor-pointer flex items-center gap-1.5
												{isSelectedTone ? 'bg-indigo-500/25 border-indigo-500/60 text-indigo-200 shadow-[0_0_10px_rgba(99,102,241,0.25)]' : 'bg-gray-950 border-gray-800 text-gray-400 hover:border-gray-700 hover:text-white'}"
										>
											<span>{t.label}</span>
											{#if isSelectedTone}<span class="text-indigo-400 font-bold">✓</span>{/if}
										</button>
									{/each}
								</div>
							</div>

							<!-- Creator Persona Multi-Select -->
							<div class="flex flex-col gap-2 pt-3 border-t border-gray-800/60">
								<div class="flex items-center justify-between">
									<span class="text-xs font-bold text-amber-300 flex items-center gap-1">
										<span>🎭 Creator Persona</span>
										<span class="text-[10px] font-normal text-gray-400">(Optional)</span>
									</span>
									<span class="text-[10px] text-gray-500 font-mono">{selectedPersonas.length} Selected</span>
								</div>
								<div class="flex flex-wrap gap-2">
									{#each availablePersonas as p}
										{@const isSelectedPersona = selectedPersonas.includes(p.id)}
										<button
											type="button"
											onclick={() => togglePersona(p.id)}
											title={p.description}
											class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all cursor-pointer flex items-center gap-1.5
												{isSelectedPersona ? 'bg-amber-500/25 border-amber-500/60 text-amber-200 shadow-[0_0_10px_rgba(245,158,11,0.25)]' : 'bg-gray-950 border-gray-800 text-gray-400 hover:border-gray-700 hover:text-white'}"
										>
											<span>{p.label}</span>
											{#if isSelectedPersona}<span class="text-amber-400 font-bold">✓</span>{/if}
										</button>
									{/each}
								</div>
							</div>

							<!-- Custom Creator Persona & Voice Input -->
							<div class="flex flex-col gap-1.5 pt-3 border-t border-gray-800/60">
								<span class="text-xs font-bold text-gray-300 flex items-center gap-1">
									<span>✍️ Custom Creator Persona / Brand Voice</span>
									<span class="text-[10px] font-normal text-gray-500">(Optional)</span>
								</span>
								<textarea
									bind:value={customPersonaInput}
									rows="2"
									placeholder="Describe your custom voice: e.g. An energetic 20-something tech reviewer with punchy Taglish slang, sarcastic wit, and bullet points."
									class="bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white placeholder-gray-600 outline-none focus:border-indigo-500 transition-all resize-y font-sans"
								></textarea>
							</div>

							<div class="flex items-center justify-end pt-2">
								<button
									type="button"
									onclick={() => subStep3 = 2}
									class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1"
								>
									<span>Next: Content Parameters ➔</span>
								</button>
							</div>
						</div>
					{/if}

					<!-- SUB-STEP 3.2: Content Generation Parameters -->
					{#if subStep3 === 2}
						<div class="flex flex-col gap-3.5 bg-gray-900/40 p-4 rounded-2xl border border-gray-800 animate-fadeIn">
							<div class="flex items-center justify-between">
								<span class="text-xs text-gray-300 font-semibold">Select Content Generation Parameters</span>
								<span class="text-[11px] text-indigo-300 font-semibold">{selectedWorkflowActions.length} Selected</span>
							</div>

							<div class="flex flex-col gap-2 max-h-[380px] overflow-y-auto pr-1">
								{#each presetWorkflows as wf}
									{@const cleanName = wf.label.replace(/^\d+\.\s*/, '')}
									{@const isSelected = selectedWorkflowActions.includes(cleanName)}
									{@const isDocExpanded = expandedDocActionId === wf.id}
									{@const isAffiliateAction = wf.id.startsWith('affiliate')}

									{#if wf.id === 'affiliate_extract'}
										<div class="my-1.5 pt-2 border-t border-gray-800 flex items-center justify-between text-[11px]">
											<span class="font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1">
												<span>🛒 Shopee Affiliate Specific Actions</span>
											</span>
											<span class="text-[10px] text-gray-500 font-mono">Affiliate Engine</span>
										</div>
									{/if}

									<div class="flex flex-col gap-2 p-3 rounded-xl border transition-all
										{isSelected ? (isAffiliateAction ? 'bg-amber-950/30 border-amber-500/50 shadow-sm' : 'bg-indigo-950/40 border-indigo-500/50 shadow-sm') : 'bg-gray-950 border-gray-800 text-gray-400 hover:border-gray-700'}">

										<div class="flex items-center justify-between gap-2">
											<button
												type="button"
												onclick={() => toggleWorkflowAction(wf.label)}
												class="flex-1 text-left text-xs font-semibold font-mono flex items-center justify-between cursor-pointer group"
											>
												<span class="group-hover:text-white transition-colors {isSelected ? (isAffiliateAction ? 'text-amber-300 font-bold' : 'text-indigo-300 font-bold') : 'text-gray-300'}">{wf.label}</span>
												<span class="text-xs px-1.5 py-0.5 rounded-md border text-[10px] font-bold
													{isSelected ? (isAffiliateAction ? 'bg-amber-500/20 border-amber-500/40 text-amber-300' : 'bg-indigo-500/20 border-indigo-500/40 text-indigo-300') : 'bg-gray-900 border-gray-800 text-gray-500'}">
													{isSelected ? '✓ Selected' : '＋ Add'}
												</span>
											</button>

											<button
												type="button"
												onclick={() => expandedDocActionId = isDocExpanded ? null : wf.id}
												title="Toggle Action Documentation & Explanation"
												class="px-2 py-1 bg-gray-900 hover:bg-gray-800 text-indigo-300 hover:text-white border border-gray-800 rounded-lg text-[10px] font-semibold flex items-center gap-1 transition-all cursor-pointer"
											>
												<span>ℹ️ Doc</span>
											</button>
										</div>

										{#if isDocExpanded}
											<div class="p-3 bg-gray-950/90 rounded-xl border border-indigo-500/30 text-xs flex flex-col gap-1.5 animate-fadeIn">
												<div class="font-bold text-white text-[11px]">📖 {wf.title}</div>
												<p class="text-[11px] text-gray-300 leading-relaxed font-sans">{wf.docDescription}</p>
												<div class="text-[10px] text-indigo-300 font-mono bg-indigo-500/10 p-2 rounded-lg border border-indigo-500/20">
													{wf.inputOutputDoc}
												</div>
											</div>
										{/if}

										{#if isSelected}
											<div class="flex flex-col gap-1 mt-1 pt-2 border-t border-indigo-500/20">
												<div class="flex items-center justify-between text-[11px]">
													<span class="text-indigo-300 font-medium">💬 Added Context / Instructions <span class="text-gray-500 font-normal text-[10px]">(Optional)</span>:</span>
												</div>
												<input
													type="text"
													bind:value={actionContexts[cleanName]}
													placeholder={wf.contextPlaceholder ? `${wf.contextPlaceholder}` : 'Add optional custom instructions...'}
													class="bg-gray-950 border border-gray-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-gray-600 outline-none focus:border-indigo-500 transition-all font-sans"
												/>
											</div>
										{/if}
									</div>
								{/each}
							</div>

							<!-- Manual Textarea Custom Instructions / Prompt Input Box -->
							<div class="flex flex-col gap-1.5 p-3 bg-gray-950 rounded-xl border border-gray-800">
								<div class="flex items-center justify-between text-xs">
									<span class="text-indigo-300 font-bold flex items-center gap-1">
										<span>📝 Manual Custom Prompt / Instructions</span>
										<span class="text-gray-400 font-normal text-[10px]">(Optional if parameters selected)</span>
									</span>
									<span class="text-[10px] text-gray-500 font-mono">Direct custom prompt</span>
								</div>
								<textarea
									bind:value={manualCustomPrompt}
									rows="3"
									placeholder="e.g. Write a short 3-line Facebook post asking community members about their favorite mechanical keyboard and WFH coffee setup today."
									class="bg-gray-900 border border-gray-800 rounded-xl p-2.5 text-xs text-white placeholder-gray-600 outline-none focus:border-indigo-500 transition-all font-sans leading-relaxed resize-none"
								></textarea>
							</div>

							<div class="flex items-center justify-between pt-2 border-t border-gray-800">
								<button
									type="button"
									onclick={() => subStep3 = 1}
									class="px-4 py-2 bg-transparent hover:bg-gray-800 text-gray-300 border border-gray-700 rounded-xl text-xs font-semibold cursor-pointer"
								>
									← Back: Tone & Persona
								</button>

								<div class="flex items-center gap-2">
									{#if !canProceedToSubStep3_3}
										<span class="text-[10px] text-amber-400 font-mono">⚠️ Select at least 1 parameter or enter manual prompt</span>
									{/if}

									<button
										type="button"
										onclick={() => {
 if (canProceedToSubStep3_3) {
subStep3 = 3;
} 
}}
										disabled={!canProceedToSubStep3_3}
										class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-800 disabled:text-gray-500 text-white rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center gap-1 disabled:cursor-not-allowed"
									>
										<span>Next: Summary & Preview ➔</span>
									</button>
								</div>
							</div>
						</div>
					{/if}

					<!-- SUB-STEP 3.3: Summary & Preview -->
					{#if subStep3 === 3}
						{@const evalData = getEvaluatedPromptPreview()}
						<div class="flex flex-col gap-4 bg-gray-900/40 p-4 rounded-2xl border border-gray-800 animate-fadeIn">
							<div class="flex items-center justify-between border-b border-gray-800 pb-3">
								<div>
									<h4 class="font-bold text-white text-sm">📋 Rule Configuration Summary</h4>
									<p class="text-xs text-gray-400">Review settings, generate preview, or save rule.</p>
								</div>

								<button
									type="button"
									onclick={testGeneratePreview}
									disabled={selectedWorkflowActions.length === 0}
									class="px-3.5 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 border border-indigo-500/50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
								>
									<span>👁️ Preview Content Modal ↗</span>
								</button>
							</div>

							<div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
								<div class="p-3 bg-gray-950 rounded-xl border border-gray-800/80 flex flex-col gap-1.5">
									<span class="text-indigo-300 font-bold">⚙️ Schedule Details</span>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Name:</span> <span class="font-semibold text-white">{newRuleName || 'Untitled'}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Category:</span> <span class="text-indigo-300">{newRuleCategory}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Frequency:</span> <span class="capitalize">{newRuleFrequency}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Times:</span> <span class="font-mono text-indigo-300">{manualTimeSlots.join(', ')}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Target Page:</span> <span class="text-emerald-400 font-semibold">{newRulePage}</span></div>
								</div>

								<div class="p-3 bg-gray-950 rounded-xl border border-gray-800/80 flex flex-col gap-1.5">
									<span class="text-indigo-300 font-bold">🎨 Persona & Pipeline</span>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Selected Tones:</span> <span class="font-semibold text-white">{selectedTones.join(', ')}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Personas:</span> <span class="text-amber-300">{selectedPersonas.join(', ')}</span></div>
									<div class="flex justify-between text-gray-300"><span class="text-gray-500">Actions:</span> <span class="text-indigo-300 font-bold">{selectedWorkflowActions.length} Steps</span></div>
									{#if customPersonaInput.trim()}
										<div class="text-[11px] text-gray-400 italic pt-1 border-t border-gray-800">"Voice: {customPersonaInput}"</div>
									{/if}
								</div>
							</div>

							<!-- Evaluated AI Prompt Payload & Optimization Hints Box -->
							<div class="flex flex-col gap-2 p-3.5 bg-indigo-950/40 border border-indigo-500/40 rounded-xl">
								<div class="flex items-center justify-between">
									<span class="text-xs font-bold text-indigo-300 flex items-center gap-1.5 font-mono">
										<span>🧠 Evaluated AI Prompt Payload</span>
									</span>
									<span class="text-[10px] text-gray-400 font-mono">Evaluated before sending to AI Engine</span>
								</div>

								<div class="p-2.5 bg-gray-950 rounded-lg border border-gray-800 text-[11px] font-mono text-gray-300 whitespace-pre-wrap leading-relaxed max-h-36 overflow-y-auto">
									{evalData.prompt}
								</div>

								<!-- Optimization Hints -->
								<div class="flex flex-col gap-1 pt-1.5 border-t border-indigo-500/20">
									<span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider font-mono">💡 Prompt Evaluation & Hints:</span>
									{#each evalData.hints as hint}
										<div class="text-[11px] text-gray-300 font-sans leading-snug">{hint}</div>
									{/each}
								</div>
							</div>

							<div class="flex items-center justify-between pt-3 border-t border-gray-800">
								<button
									type="button"
									onclick={() => subStep3 = 2}
									class="px-4 py-2 bg-transparent hover:bg-gray-800 text-gray-300 border border-gray-700 rounded-xl text-xs font-semibold cursor-pointer"
								>
									← Back: Action Steps
								</button>

								<button
									type="button"
									onclick={handleSaveSchedule}
									class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-emerald-600/30 transition-all active:scale-95 cursor-pointer"
								>
									Save & Activate Rule 🚀
								</button>
							</div>
						</div>
					{/if}
				</div>
			{/if}

			<!-- Wizard Navigation Footer -->
			<div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-800">
				<div>
					{#if wizardStep > 1}
						<button
							type="button"
							onclick={() => wizardStep = (wizardStep - 1) as any}
							class="px-4 py-2 bg-transparent hover:bg-gray-800 text-gray-300 rounded-xl text-xs font-semibold border border-gray-700 transition-all cursor-pointer"
						>
							← Back
						</button>
					{/if}
				</div>

				<div class="flex items-center gap-2">
					<button
						type="button"
						onclick={() => showNewScheduleModal = false}
						class="px-4 py-2 rounded-xl text-xs text-gray-400 hover:text-white cursor-pointer"
					>
						Cancel
					</button>

					{#if wizardStep < 3}
						<button
							type="button"
							onclick={() => wizardStep = (wizardStep + 1) as any}
							class="px-5 py-2 bg-transparent hover:bg-indigo-500/10 text-indigo-300 border border-indigo-500/60 hover:border-indigo-400 font-semibold rounded-xl text-xs hover:shadow-[0_0_12px_rgba(99,102,241,0.4)] transition-all cursor-pointer"
						>
							Next ➔
						</button>
					{:else}
						<button
							type="button"
							onclick={handleSaveSchedule}
							class="px-5 py-2 bg-transparent hover:bg-emerald-500/10 text-emerald-300 border border-emerald-500/60 hover:border-emerald-400 font-semibold rounded-xl text-xs hover:shadow-[0_0_12px_rgba(16,185,129,0.4)] transition-all cursor-pointer"
						>
							{editingRuleId ? 'Update Schedule Rule 🚀' : 'Save & Activate Rule 🚀'}
						</button>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/if}

<!-- Modal: New/Edit Event Trigger -->
{#if showNewTriggerModal}
	{@const triggerInfo = getEventTriggerHintAndPayload()}
	{@const setupInfo = getIntegrationInstructions()}
	<div
		class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fadeIn"
		onclick={() => showNewTriggerModal = false}
		role="dialog"
		aria-modal="true"
	>
		<div
			class="bg-gray-950 border border-amber-500/30 rounded-2xl p-6 max-w-md w-full flex flex-col gap-4 relative shadow-2xl"
			onclick={(e) => e.stopPropagation()}
		>
			<div class="flex items-center justify-between">
				<h3 class="font-bold text-white text-lg flex items-center gap-2">
					<span class="text-amber-400">⚡</span>
					<span>{editingTriggerId ? 'Edit Event Trigger' : 'Create Event Trigger'}</span>
				</h3>
				<button
					type="button"
					onclick={() => showNewTriggerModal = false}
					class="text-gray-400 hover:text-white font-bold text-sm cursor-pointer"
				>
					✕
				</button>
			</div>

			<div class="flex flex-col gap-1.5">
				<label class="text-xs text-gray-400 font-medium" for="trigger_name">Trigger Name</label>
				<input
					id="trigger_name"
					type="text"
					bind:value={newTriggerName}
					placeholder="Shopee Price Drop > 40% OFF Alert"
					class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-amber-500"
				/>
			</div>

			<div class="flex flex-col gap-1.5">
				<span class="text-xs text-gray-400 font-medium">Event Source</span>
				<div class="relative">
					<button
						type="button"
						onclick={() => triggerSourceDropdownOpen = !triggerSourceDropdownOpen}
						class="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-amber-500 flex items-center justify-between text-left cursor-pointer"
					>
						<span>{triggerSourceOptions.find(o => o.value === newTriggerSource)?.label || newTriggerSource}</span>
						<span class="text-[10px] text-gray-400">{triggerSourceDropdownOpen ? '▲' : '▼'}</span>
					</button>

					{#if triggerSourceDropdownOpen}
						<div class="absolute z-50 left-0 right-0 mt-1 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col divide-y divide-gray-800/60 max-h-48 overflow-y-auto">
							{#each triggerSourceOptions as opt}
								<button
									type="button"
									onclick={() => {
 newTriggerSource = opt.value; triggerSourceDropdownOpen = false; 
}}
									class="px-3 py-2.5 text-xs text-left transition-colors flex items-center justify-between cursor-pointer
										{newTriggerSource === opt.value ? 'bg-amber-500/20 text-amber-300 font-semibold' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white'}"
								>
									<span>{opt.label}</span>
									{#if newTriggerSource === opt.value}
										<span class="text-amber-400 font-bold">✓</span>
									{/if}
								</button>
							{/each}
						</div>
					{/if}
				</div>
			</div>

			<div class="flex flex-col gap-1.5">
				<label class="text-xs text-gray-400 font-medium" for="trigger_condition">Trigger Condition</label>
				<input
					id="trigger_condition"
					type="text"
					bind:value={newTriggerCondition}
					placeholder="Discount >= 40% OFF or Comment contains 'HM' / 'LINK'"
					class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-amber-500"
				/>
			</div>

			<div class="flex flex-col gap-1.5">
				<label class="text-xs text-gray-400 font-medium" for="trigger_target_page">Target Social Page</label>
				<select
					id="trigger_target_page"
					bind:value={newTriggerTargetPage}
					class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-amber-500 cursor-pointer"
				>
					<option value="Tech Sulit Deals">Tech Sulit Deals (Facebook Page)</option>
					{#each connectedAccounts as acc}
						{#if acc.name !== 'Tech Sulit Deals'}
							<option value={acc.name}>{acc.name} ({acc.platform})</option>
						{/if}
					{/each}
				</select>
			</div>

			<div class="flex flex-col gap-1.5">
				<label class="text-xs text-gray-400 font-medium" for="trigger_action_pipeline">Trigger Action Pipeline</label>
				<select
					id="trigger_action_pipeline"
					bind:value={newTriggerAction}
					class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-xs text-white outline-none focus:border-amber-500 cursor-pointer"
				>
					<option value="Auto Extract & Instant Publish">⚡ Auto Extract & Instant Publish to Facebook</option>
					<option value="Fan Comment Auto-Reply">💬 Fan Comment Auto-Reply with Buy Link</option>
					<option value="n8n Outbound Webhook Dispatch">🌐 Dispatch Outbound Webhook to n8n</option>
					<option value="Voucher Code Expiry Alert">🎟️ Voucher Code Expiry Warning</option>
				</select>
			</div>

			<!-- Dynamic Action-Specific Configuration Fields -->
			{#if newTriggerAction === 'Auto Extract & Instant Publish'}
				<div class="flex flex-col gap-3 p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl animate-fadeIn">
					<span class="text-[11px] font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1">
						<span>⚡ Auto Extract & Publish Parameters</span>
					</span>
					
					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_shopee_url">Default Shopee Product URL</label>
						<input
							id="trigger_shopee_url"
							type="text"
							bind:value={triggerShopeeUrl}
							placeholder="https://s.shopee.ph/60QPnzrXoO"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-amber-400 font-mono"
						/>
					</div>
				</div>

			{:else if newTriggerAction === 'Fan Comment Auto-Reply'}
				<div class="flex flex-col gap-3 p-3 bg-purple-500/10 border border-purple-500/30 rounded-xl animate-fadeIn">
					<span class="text-[11px] font-bold text-purple-300 uppercase tracking-wider flex items-center gap-1">
						<span>💬 Fan Comment Auto-Reply Parameters</span>
					</span>
					
					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_keywords">Trigger Keywords (Comma separated)</label>
						<input
							id="trigger_keywords"
							type="text"
							bind:value={triggerKeywords}
							placeholder="HM, LINK, PM, PRICE, MAGKANO, SULIT, ORDER"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-purple-400 font-mono"
						/>
					</div>

					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_reply_template">Auto-Reply Message Template</label>
						<textarea
							id="trigger_reply_template"
							bind:value={triggerReplyTemplate}
							rows="2"
							placeholder="Hi &#123;user_name&#125;! 👋 Here is the official buy link: 🛒 &#123;buy_link&#125; #TechSulitDeals"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-purple-400 font-mono resize-y"
						></textarea>
					</div>
				</div>

			{:else if newTriggerAction === 'n8n Outbound Webhook Dispatch'}
				<div class="flex flex-col gap-3 p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl animate-fadeIn">
					<span class="text-[11px] font-bold text-blue-300 uppercase tracking-wider flex items-center gap-1">
						<span>🌐 Outbound Webhook Parameters</span>
					</span>
					
					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_webhook_url">n8n / Receiver Webhook HTTP URL</label>
						<input
							id="trigger_webhook_url"
							type="text"
							bind:value={triggerWebhookUrl}
							placeholder="https://n8n.example.com/webhook/deal-alert"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-blue-400 font-mono"
						/>
					</div>

					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_webhook_secret">Webhook Secret Auth Token</label>
						<input
							id="trigger_webhook_secret"
							type="password"
							bind:value={triggerWebhookSecret}
							placeholder="e.g. sec_n8n_aiffiliate_2026"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-blue-400 font-mono"
						/>
					</div>
				</div>

			{:else if newTriggerAction === 'Voucher Code Expiry Alert'}
				<div class="flex flex-col gap-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl animate-fadeIn">
					<span class="text-[11px] font-bold text-emerald-300 uppercase tracking-wider flex items-center gap-1">
						<span>🎟️ Voucher Expiry Alert Parameters</span>
					</span>
					
					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_voucher_code">Promo Voucher Code</label>
						<input
							id="trigger_voucher_code"
							type="text"
							bind:value={triggerVoucherCode}
							placeholder="ANKERTECH88 or PAYDAY100"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-emerald-400 font-mono"
						/>
					</div>

					<div class="flex flex-col gap-1">
						<label class="text-[11px] text-gray-300 font-medium" for="trigger_voucher_discount">Discount Offer Description</label>
						<input
							id="trigger_voucher_discount"
							type="text"
							bind:value={triggerVoucherDiscount}
							placeholder="40% OFF with no min spend up to ₱500"
							class="bg-gray-900 border border-gray-800 rounded-lg p-2 text-xs text-white outline-none focus:border-emerald-400"
						/>
					</div>
				</div>
			{/if}

			<!-- Evaluated Trigger Hints & Sample Input Payload Preview -->
			<div class="flex flex-col gap-2 p-3 bg-gray-900 border border-gray-800 rounded-xl">
				<div class="flex items-center justify-between">
					<span class="text-xs font-bold text-amber-300 flex items-center gap-1.5 font-mono">
						<span>🧪 Sample Event Input Payload (Evaluated by AI)</span>
					</span>
					<span class="text-[10px] text-gray-400 font-mono">JSON Sample</span>
				</div>

				<pre class="p-2.5 bg-gray-950 rounded-lg border border-gray-800/80 text-[10px] font-mono text-amber-200/90 whitespace-pre-wrap leading-relaxed max-h-32 overflow-y-auto">{triggerInfo.examplePayload}</pre>

				<div class="text-[11px] text-gray-300 font-sans leading-snug pt-1 border-t border-gray-800">
					{triggerInfo.hint}
				</div>
			</div>

			<!-- Integration Instructions & Setup Hints Box -->
			<div class="flex flex-col gap-2 p-3 bg-amber-950/30 border border-amber-500/40 rounded-xl">
				<div class="flex items-center justify-between">
					<span class="text-xs font-bold text-amber-300 flex items-center gap-1.5 font-mono">
						<span>📖 Integration & Setup Instructions</span>
					</span>
					<span class="text-[10px] text-gray-400 font-mono">Setup Guide</span>
				</div>

				<div class="p-2 bg-gray-950 rounded-lg border border-gray-800 text-[10px] font-mono text-emerald-300 font-semibold truncate">
					Endpoint: {setupInfo.endpoint}
				</div>

				<div class="flex flex-col gap-1 text-[11px] text-gray-300 font-sans leading-snug">
					{#each setupInfo.instructions as stepStr}
						<div>{stepStr}</div>
					{/each}
				</div>
			</div>

			<div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-800">
				<button
					type="button"
					onclick={() => showNewTriggerModal = false}
					class="px-4 py-2 rounded-xl text-xs text-gray-400 hover:text-white cursor-pointer"
				>
					Cancel
				</button>
				<button
					type="button"
					onclick={handleAddTrigger}
					class="px-5 py-2 bg-transparent hover:bg-amber-500/10 text-amber-300 border border-amber-500/60 hover:border-amber-400 font-semibold rounded-xl text-xs hover:shadow-[0_0_12px_rgba(245,158,11,0.4)] transition-all cursor-pointer"
				>
					{editingTriggerId ? 'Update Event Trigger 🚀' : 'Save Event Trigger 🚀'}
				</button>
			</div>
		</div>
	</div>
{/if}

	</div>
</AppLayout>
