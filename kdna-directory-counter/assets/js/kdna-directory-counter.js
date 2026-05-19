/**
 * KDNA Directory Counter front-end behaviour.
 *
 * Handles overlay injection into a target element, position presets,
 * Elementor editor placeholder rendering, and JetSmartFilters live
 * count updates via AJAX.
 */
( function () {
	'use strict';

	var SELECTOR = '.kdna-directory-counter';
	var EDITOR_BADGE_CLASS = 'kdna-directory-counter__editor-badge';

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

	function showInline( counter ) {
		counter.style.display = '';
	}

	function applyPosition( counter, config ) {
		counter.style.top = '';
		counter.style.right = '';
		counter.style.bottom = '';
		counter.style.left = '';

		var v = config.offsets.vertical || '20px';
		var h = config.offsets.horizontal || '20px';

		switch ( config.positionPreset ) {
			case 'top-left':
				counter.style.top = v;
				counter.style.left = h;
				break;
			case 'top-right':
				counter.style.top = v;
				counter.style.right = h;
				break;
			case 'bottom-left':
				counter.style.bottom = v;
				counter.style.left = h;
				break;
			case 'bottom-right':
				counter.style.bottom = v;
				counter.style.right = h;
				break;
			case 'custom':
				if ( config.offsets.top ) {
					counter.style.top = config.offsets.top;
				}
				if ( config.offsets.right ) {
					counter.style.right = config.offsets.right;
				}
				if ( config.offsets.bottom ) {
					counter.style.bottom = config.offsets.bottom;
				}
				if ( config.offsets.left ) {
					counter.style.left = config.offsets.left;
				}
				break;
		}

		counter.style.position = 'absolute';
		counter.style.zIndex = String( config.zIndex || 10 );
	}

	function ensureTargetPositioned( target ) {
		var computed = window.getComputedStyle( target ).position;
		if ( computed === 'static' || computed === '' ) {
			target.style.position = 'relative';
		}
	}

	function injectIntoTarget( counter, target, config ) {
		ensureTargetPositioned( target );
		if ( counter.parentNode !== target ) {
			target.appendChild( counter );
		}
		applyPosition( counter, config );
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

	function updateLabel( counter, count, config ) {
		var labelEl = counter.querySelector( '.kdna-directory-counter__label' );
		if ( ! labelEl ) {
			return;
		}
		labelEl.textContent = ( 1 === count ) ? config.singularLabel : config.pluralLabel;
	}

	function updateNumber( counter, count ) {
		var numberEl = counter.querySelector( '.kdna-directory-counter__number' );
		if ( ! numberEl ) {
			return;
		}
		numberEl.textContent = String( count );
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

		var editor = isEditorMode();

		if ( editor ) {
			renderEditorPlaceholder( counter, config );
			return;
		}

		var targetId = config.targetElementId;

		if ( ! targetId ) {
			showInline( counter );
			return;
		}

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

		if ( window.jQuery ) {
			window.jQuery( window ).on( 'jet-smart-filters/render-ended', function () {
				handleJsfRender( counter, config );
			} );
		} else if ( document.addEventListener ) {
			document.addEventListener( 'jet-smart-filters/render-ended', function () {
				handleJsfRender( counter, config );
			} );
		}
	}

	function handleJsfRender( counter, config ) {
		if ( config.enableAbsolute && config.targetElementId ) {
			var target = document.getElementById( config.targetElementId );
			if ( target && ! target.contains( counter ) ) {
				injectIntoTarget( counter, target, config );
			}
		}

		if ( config.source === 'jsf_query' ) {
			fetchJsfCount( config, function ( count ) {
				updateNumber( counter, count );
				updateLabel( counter, count, config );
			} );
		}
	}

	function initAll() {
		var counters = document.querySelectorAll( SELECTOR );
		Array.prototype.forEach.call( counters, initCounter );

		if ( ! initAll._jsfBound && window.jQuery ) {
			initAll._jsfBound = true;
			window.jQuery( window ).on( 'jet-smart-filters/render-ended', function () {
				var nodes = document.querySelectorAll( SELECTOR );
				Array.prototype.forEach.call( nodes, function ( c ) {
					var cfg = getConfig( c );
					if ( cfg ) {
						handleJsfRender( c, cfg );
					}
				} );
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
}() );
