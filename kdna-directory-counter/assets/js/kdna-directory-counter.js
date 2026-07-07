/**
 * KDNA Directory Counter front-end behaviour.
 *
 * Handles overlay injection into a target element, position presets,
 * Elementor editor placeholder rendering, JetSmartFilters live count
 * updates via DOM item count with AJAX fallback, and CountUp.js
 * animation with viewport detection.
 */
( function () {
	'use strict';

	var SELECTOR = '.kdna-directory-counter';
	var EDITOR_BADGE_CLASS = 'kdna-directory-counter__editor-badge';
	var ITEM_SELECTOR = '.jet-listing-grid__item';

	function getConfig( counter ) {
		var raw = counter.getAttribute( 'data-kdna-config' );
		if ( ! raw ) {
			return null;
		}
		try {
			return JSON.parse( raw );
		} catch ( e ) {
			return null;
		}
	}

	function isEditorMode() {
		if ( window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' ) {
			try {
				if ( window.elementorFrontend.isEditMode() ) {
					return true;
				}
			} catch ( e ) {}
		}
		return !! document.body.classList.contains( 'elementor-editor-active' );
	}

	/**
	 * Return the element that should be moved into the overlay target.
	 * We move the entire Elementor widget wrapper so all styles compiled
	 * against {{WRAPPER}} .kdna-directory-counter still apply after the
	 * counter has been relocated. Falls back to the counter itself when
	 * the wrapper cannot be found.
	 */
	function getWidgetWrapper( counter ) {
		if ( counter.closest ) {
			var wrapper = counter.closest( '.elementor-element' );
			if ( wrapper ) {
				return wrapper;
			}
		}
		return counter;
	}

	function showInline( counter ) {
		counter.style.display = '';
	}

	function applyPosition( el, config ) {
		el.style.top = '';
		el.style.right = '';
		el.style.bottom = '';
		el.style.left = '';

		var v = config.offsets.vertical || '20px';
		var h = config.offsets.horizontal || '20px';

		switch ( config.positionPreset ) {
			case 'top-left':
				el.style.top = v;
				el.style.left = h;
				break;
			case 'top-right':
				el.style.top = v;
				el.style.right = h;
				break;
			case 'bottom-left':
				el.style.bottom = v;
				el.style.left = h;
				break;
			case 'bottom-right':
				el.style.bottom = v;
				el.style.right = h;
				break;
			case 'custom':
				if ( config.offsets.top ) {
					el.style.top = config.offsets.top;
				}
				if ( config.offsets.right ) {
					el.style.right = config.offsets.right;
				}
				if ( config.offsets.bottom ) {
					el.style.bottom = config.offsets.bottom;
				}
				if ( config.offsets.left ) {
					el.style.left = config.offsets.left;
				}
				break;
		}

		el.style.position = 'absolute';
		el.style.width = 'auto';
		el.style.margin = '0';
		el.style.zIndex = String( config.zIndex || 10 );
	}

	function ensureTargetPositioned( target ) {
		var computed = window.getComputedStyle( target ).position;
		if ( computed === 'static' || computed === '' ) {
			target.style.position = 'relative';
		}
	}

	function injectIntoTarget( counter, target, config ) {
		ensureTargetPositioned( target );
		var wrapper = getWidgetWrapper( counter );
		if ( wrapper.parentNode !== target ) {
			target.appendChild( wrapper );
		}
		applyPosition( wrapper, config );
		counter.style.display = '';
	}

	function renderEditorPlaceholder( counter, config ) {
		if ( counter.querySelector( '.' + EDITOR_BADGE_CLASS ) ) {
			counter.style.display = '';
			return;
		}

		var badge = document.createElement( 'span' );
		badge.className = EDITOR_BADGE_CLASS;
		badge.textContent = config.targetElementId
			? 'Overlays #' + config.targetElementId + ' on the front end'
			: 'Inline mode (no target)';

		counter.insertBefore( badge, counter.firstChild );
		counter.style.display = '';
	}

	function getNumberEl( counter ) {
		return counter.querySelector( '.kdna-directory-counter__number' );
	}

	function getLabelEl( counter ) {
		return counter.querySelector( '.kdna-directory-counter__label' );
	}

	function setLabel( counter, count, config ) {
		var labelEl = getLabelEl( counter );
		if ( ! labelEl ) {
			return;
		}
		labelEl.textContent = ( 1 === count ) ? config.singularLabel : config.pluralLabel;
	}

	/**
	 * Return the number of visible JetEngine Listing Grid items associated
	 * with this counter, or null if none can be found.
	 *
	 * Search order:
	 *   1. Element with the ID from the JSF query / listing CSS ID field
	 *   2. Element with the ID from the overlay target
	 *   3. Page-wide fallback
	 */
	function getDomItemCount( config ) {
		var candidates = [];
		if ( config.jsfQueryId ) {
			var a = document.getElementById( config.jsfQueryId );
			if ( a ) {
				candidates.push( a );
			}
		}
		if ( config.targetElementId ) {
			var b = document.getElementById( config.targetElementId );
			if ( b ) {
				candidates.push( b );
			}
		}
		for ( var i = 0; i < candidates.length; i++ ) {
			var items = candidates[ i ].querySelectorAll( ITEM_SELECTOR );
			if ( items.length > 0 ) {
				return items.length;
			}
		}
		var globalItems = document.querySelectorAll( ITEM_SELECTOR );
		if ( globalItems.length > 0 ) {
			return globalItems.length;
		}
		return null;
	}

	function createCountUp( numberEl, endVal, config, startVal ) {
		if ( ! window.CountUp ) {
			numberEl.textContent = String( endVal );
			return null;
		}
		return new window.CountUp( numberEl, endVal, {
			startVal: typeof startVal === 'number' ? startVal : 0,
			duration: config.animationDuration,
			easingFn: config.animationEasing,
			useEasing: true,
			separator: ','
		} );
	}

	function animateInitial( counter, config ) {
		var numberEl = getNumberEl( counter );
		if ( ! numberEl ) {
			return;
		}
		var finalVal = parseInt( numberEl.getAttribute( 'data-kdna-final' ), 10 ) || config.finalCount || 0;

		if ( config.source === 'jsf_query' ) {
			var domCount = getDomItemCount( config );
			if ( typeof domCount === 'number' ) {
				finalVal = domCount;
				setLabel( counter, finalVal, config );
			}
		}

		if ( ! config.enableAnimation ) {
			numberEl.textContent = finalVal.toLocaleString();
			counter._kdnaLastValue = finalVal;
			return;
		}

		var instance = createCountUp( numberEl, finalVal, config, 0 );
		if ( instance && ! instance.error ) {
			instance.start();
			counter._kdnaCountUp = instance;
		} else {
			numberEl.textContent = finalVal.toLocaleString();
		}
		counter._kdnaLastValue = finalVal;
	}

	function animateTo( counter, newVal, config ) {
		var numberEl = getNumberEl( counter );
		if ( ! numberEl ) {
			return;
		}

		if ( ! config.enableAnimation ) {
			numberEl.textContent = newVal.toLocaleString();
			counter._kdnaLastValue = newVal;
			return;
		}

		var prev = ( typeof counter._kdnaLastValue === 'number' ) ? counter._kdnaLastValue : 0;

		if ( counter._kdnaCountUp && typeof counter._kdnaCountUp.update === 'function' ) {
			counter._kdnaCountUp.update( newVal );
		} else {
			var instance = createCountUp( numberEl, newVal, config, prev );
			if ( instance && ! instance.error ) {
				instance.start();
				counter._kdnaCountUp = instance;
			} else {
				numberEl.textContent = newVal.toLocaleString();
			}
		}
		counter._kdnaLastValue = newVal;
	}

	function observeForAnimation( counter, config ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			animateInitial( counter, config );
			return;
		}

		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					animateInitial( counter, config );
					obs.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.2 } );

		observer.observe( counter );
	}

	function fetchJsfCount( config, onSuccess ) {
		if ( ! window.kdnaDirectoryCounter || ! window.kdnaDirectoryCounter.ajax_url ) {
			return;
		}
		if ( ! config.jsfQueryId ) {
			return;
		}

		var body = new URLSearchParams();
		body.append( 'action', 'kdna_directory_counter_get_count' );
		body.append( 'nonce', window.kdnaDirectoryCounter.nonce );
		body.append( 'jsf_query_id', config.jsfQueryId );

		window.fetch( window.kdnaDirectoryCounter.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		} ).then( function ( res ) {
			return res.json();
		} ).then( function ( data ) {
			if ( data && data.success && data.data && typeof data.data.count !== 'undefined' ) {
				onSuccess( parseInt( data.data.count, 10 ) || 0 );
			}
		} ).catch( function () {} );
	}

	function handleJsfRender( counter, config ) {
		if ( config.enableAbsolute && config.targetElementId ) {
			var target = document.getElementById( config.targetElementId );
			var wrapper = getWidgetWrapper( counter );
			if ( target && ! target.contains( wrapper ) ) {
				injectIntoTarget( counter, target, config );
			}
		}

		if ( config.source === 'jsf_query' ) {
			var domCount = getDomItemCount( config );
			if ( typeof domCount === 'number' ) {
				animateTo( counter, domCount, config );
				setLabel( counter, domCount, config );
				return;
			}
			fetchJsfCount( config, function ( count ) {
				animateTo( counter, count, config );
				setLabel( counter, count, config );
			} );
		}
	}

	function bindJsfListener( counter, config ) {
		var handler = function () {
			handleJsfRender( counter, config );
		};
		if ( window.jQuery ) {
			window.jQuery( window ).on( 'jet-smart-filters/render-ended', handler );
		} else if ( document.addEventListener ) {
			document.addEventListener( 'jet-smart-filters/render-ended', handler );
		}
	}

	function initCounter( counter ) {
		if ( counter.dataset.kdnaInitialised === '1' ) {
			return;
		}
		counter.dataset.kdnaInitialised = '1';

		var config = getConfig( counter );
		if ( ! config ) {
			counter.style.display = '';
			return;
		}

		if ( isEditorMode() ) {
			renderEditorPlaceholder( counter, config );
			return;
		}

		var targetId = config.targetElementId;

		if ( ! targetId ) {
			showInline( counter );
		} else {
			var target = document.getElementById( targetId );
			if ( ! target ) {
				if ( window.console && window.console.warn ) {
					window.console.warn( 'KDNA Directory Counter: target element #' + targetId + ' not found' );
				}
				return;
			}
			if ( config.enableAbsolute ) {
				injectIntoTarget( counter, target, config );
			} else {
				showInline( counter );
			}
		}

		observeForAnimation( counter, config );
		bindJsfListener( counter, config );
	}

	function initAll() {
		var counters = document.querySelectorAll( SELECTOR );
		Array.prototype.forEach.call( counters, initCounter );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
