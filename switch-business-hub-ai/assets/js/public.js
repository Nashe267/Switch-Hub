/**
 * Switch Business Hub AI - Premium App JavaScript v1.4.0
 * Complete single-page app with AI assistant & all features
 * FIXED: All button interactions and event handlers
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(function() {
        console.log('🚀 SwitchHub v1.4.0 initializing...');

        // ==================== CONFIGURATION ====================
        var config = {
            ajaxUrl: '',
            nonce: '',
            debug: true
        };

        // Get AJAX settings from WordPress or DOM
        if (typeof sbhaPublic !== 'undefined') {
            config.ajaxUrl = sbhaPublic.ajaxUrl;
            config.nonce = sbhaPublic.nonce;
        }

        // Fallback to DOM data attributes
        var $app = $('#switch-hub-app');
        if ($app.length) {
            if (!config.ajaxUrl) config.ajaxUrl = $app.data('ajax');
            if (!config.nonce) config.nonce = $app.data('nonce');
        }

        // Final fallback
        if (!config.ajaxUrl) {
            config.ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
        }

        function log(msg, data) {
            if (config.debug) {
                if (data) {
                    console.log('📱 SwitchHub:', msg, data);
                } else {
                    console.log('📱 SwitchHub:', msg);
                }
            }
        }

        log('Config loaded', { ajax: config.ajaxUrl, nonce: config.nonce ? '✓' : '✗' });

        // ==================== UTILITY FUNCTIONS ====================

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showToast(type, msg) {
            var $container = $('#sh-toasts');
            if (!$container.length) {
                $container = $('<div id="sh-toasts" class="sh-toasts"></div>');
                $('body').append($container);
            }

            var icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
            var $toast = $('<div class="sh-toast sh-toast-' + type + '">' + icon + ' ' + msg + '</div>');
            $container.append($toast);

            setTimeout(function() {
                $toast.fadeOut(300, function() { $(this).remove(); });
            }, 4000);
        }

        function showLoading($btn) {
            $btn.data('original-text', $btn.html());
            $btn.prop('disabled', true).html('<span class="sh-spinner"></span> Loading...');
        }

        function hideLoading($btn) {
            var originalText = $btn.data('original-text');
            $btn.prop('disabled', false).html(originalText || 'Submit');
        }

        // ==================== PANEL NAVIGATION ====================

        function switchPanel(panel) {
            log('Switching to panel:', panel);
            
            // Update nav buttons
            $('.sh-nav-btn').removeClass('active');
            $('.sh-nav-btn[data-panel="' + panel + '"]').addClass('active');
            
            // Update panels
            $('.sh-panel').removeClass('active');
            var $targetPanel = $('#panel-' + panel);
            
            if ($targetPanel.length) {
                $targetPanel.addClass('active');
                log('Panel activated:', '#panel-' + panel);
            } else {
                log('Panel not found:', '#panel-' + panel);
            }

            // Scroll to main content
            var $main = $('.sh-main');
            if ($main.length) {
                $('html, body').animate({ scrollTop: $main.offset().top - 60 }, 300);
            }
        }

        // Bottom navigation - use event delegation
        $(document).on('click', '.sh-nav-btn[data-panel]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var panel = $(this).data('panel');
            log('Nav button clicked:', panel);
            switchPanel(panel);
        });

        // AI center button
        $(document).on('click', '.sh-nav-ai, #nav-ai', function(e) {
            e.preventDefault();
            e.stopPropagation();
            log('AI button clicked');
            $('html, body').animate({ scrollTop: 0 }, 300, function() {
                $('#ai-input').focus();
            });
        });

        // ==================== AI CHAT ====================

        function sendAIMessage() {
            var $input = $('#ai-input');
            var query = $input.val().trim();

            if (!query) {
                log('Empty query, ignoring');
                return;
            }

            log('Sending AI message:', query);

            var $chat = $('#ai-chat');

            // Add user message
            $chat.append('<div class="sh-msg sh-msg-user">' + escapeHtml(query) + '</div>');
            $input.val('');

            // Show typing indicator
            $chat.append('<div class="sh-msg sh-msg-bot sh-typing"><p>🤔 Thinking...</p></div>');
            $chat.scrollTop($chat[0].scrollHeight);

            // Send to server
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sbha_ai_chat',
                    message: query,
                    nonce: config.nonce
                },
                success: function(res) {
                    log('AI response:', res);
                    $chat.find('.sh-typing').remove();

                    var response = '';
                    if (res.success && res.data && res.data.response) {
                        response = '<p>' + res.data.response + '</p>';
                    } else {
                        response = '<p>I\'d be happy to help! Try asking about our services, requesting a quote, or tracking an order.</p>';
                    }

                    response += '<div class="sh-quick-btns" style="margin-top:12px">';
                    response += '<button type="button" data-q="Get me a quote">Get Quote</button>';
                    response += '<button type="button" data-q="Track my order">Track Order</button>';
                    response += '</div>';

                    $chat.append('<div class="sh-msg sh-msg-bot">' + response + '</div>');
                    $chat.scrollTop($chat[0].scrollHeight);
                },
                error: function(xhr, status, error) {
                    log('AI error:', error);
                    $chat.find('.sh-typing').remove();
                    $chat.append('<div class="sh-msg sh-msg-bot"><p>Sorry, I\'m having trouble connecting. Please try the menu below.</p></div>');
                    $chat.scrollTop($chat[0].scrollHeight);
                }
            });
        }

        // AI Send button
        $(document).on('click', '#ai-send', function(e) {
            e.preventDefault();
            log('AI send clicked');
            sendAIMessage();
        });

        // AI Input enter key
        $(document).on('keypress', '#ai-input', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                log('AI enter pressed');
                sendAIMessage();
            }
        });

        // Quick buttons - use delegation for dynamic buttons
        $(document).on('click', '.sh-quick-btns button', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var q = $(this).data('q');
            log('Quick button clicked:', q);
            if (q) {
                $('#ai-input').val(q);
                sendAIMessage();
            }
        });

        // ==================== SERVICE FILTERS ====================

        $(document).on('click', '.sh-filter', function(e) {
            e.preventDefault();
            var cat = $(this).data('cat');
            log('Filter clicked:', cat);

            $('.sh-filter').removeClass('active');
            $(this).addClass('active');

            if (cat === 'all') {
                $('.sh-service').removeClass('hidden').show();
            } else {
                $('.sh-service').each(function() {
                    var serviceCat = $(this).data('cat');
                    if (serviceCat === cat) {
                        $(this).removeClass('hidden').show();
                    } else {
                        $(this).addClass('hidden').hide();
                    }
                });
            }
        });

        // ==================== GET QUOTE BUTTONS ====================

        $(document).on('click', '.sh-get-quote', function(e) {
            e.preventDefault();
            var $card = $(this).closest('.sh-service');
            var id = $card.data('id');
            log('Get Quote clicked for service:', id);

            $('#quote-service').val(id).trigger('change');
            switchPanel('quote');
        });

        // Quote preview calculator
        function updateQuotePreview() {
            var $sel = $('#quote-service option:selected');
            var basePrice = parseFloat($sel.data('price')) || 0;
            var qty = parseInt($('#quote-qty').val()) || 1;
            var urgency = $('#quote-urg').val() || 'standard';
            var mult = urgency === 'express' ? 1.25 : (urgency === 'rush' ? 1.5 : 1);
            var total = basePrice * qty * mult;

            $('#pv-svc').text($sel.text().split(' - ')[0] || '-');
            $('#pv-qty').text(qty);
            $('#pv-total').text('R' + total.toFixed(2));
        }

        $(document).on('change', '#quote-service, #quote-qty, #quote-urg', function() {
            updateQuotePreview();
        });

        // Show custom field
        $(document).on('change', '#quote-service', function() {
            var val = $(this).val();
            var isCustom = val === 'custom' || val === '' || val === '0';
            $('.sh-custom-field').toggle(isCustom);
        });

        // ==================== QUOTE FORM ====================

        $(document).on('submit', '#quote-form', function(e) {
            e.preventDefault();
            log('Quote form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            showLoading($btn);

            var formData = new FormData(this);
            formData.append('action', 'sbha_submit_quote');
            formData.append('nonce', config.nonce);

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    log('Quote response:', res);
                    var $msg = $('#quote-msg');
                    
                    if (res.success) {
                        $msg.removeClass('error').addClass('sh-message success')
                            .html('✓ Quote submitted! Order: <strong>' + (res.data.order_number || 'Processing') + '</strong>').show();
                        $form[0].reset();
                        updateQuotePreview();
                        showToast('success', 'Quote submitted successfully!');
                    } else {
                        $msg.removeClass('success').addClass('sh-message error')
                            .text('✗ ' + (res.data || 'Error submitting quote')).show();
                        showToast('error', res.data || 'Error submitting quote');
                    }
                },
                error: function(xhr, status, error) {
                    log('Quote error:', error);
                    $('#quote-msg').addClass('sh-message error').text('✗ Connection error. Please try again.').show();
                    showToast('error', 'Connection error');
                },
                complete: function() {
                    hideLoading($btn);
                }
            });
        });

        // ==================== TRACK ORDER ====================

        $(document).on('submit', '#track-form', function(e) {
            e.preventDefault();
            log('Track form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var query = $('#track-input').val().trim();

            if (!query) {
                showToast('error', 'Please enter an order number or email');
                return;
            }

            showLoading($btn);

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sbha_track_order',
                    query: query,
                    nonce: config.nonce
                },
                success: function(res) {
                    log('Track response:', res);
                    var $results = $('#track-results');
                    
                    if (res.success && res.data && res.data.orders && res.data.orders.length > 0) {
                        var html = '<div class="sh-orders-list">';
                        res.data.orders.forEach(function(order) {
                            html += '<div class="sh-order-card">';
                            html += '<div class="sh-order-header">';
                            html += '<strong>#' + order.order_number + '</strong>';
                            html += '<span class="sh-status sh-status-' + order.status + '">' + order.status + '</span>';
                            html += '</div>';
                            html += '<div class="sh-order-body">';
                            html += '<p>Service: ' + (order.service_name || 'Custom') + '</p>';
                            html += '<p>Date: ' + order.created_at + '</p>';
                            if (order.total_amount) {
                                html += '<p>Total: R' + parseFloat(order.total_amount).toFixed(2) + '</p>';
                            }
                            html += '</div></div>';
                        });
                        html += '</div>';
                        $results.html(html);
                        showToast('success', 'Orders found!');
                    } else {
                        $results.html('<div class="sh-message info">No orders found for "' + escapeHtml(query) + '"</div>');
                    }
                },
                error: function() {
                    $('#track-results').html('<div class="sh-message error">Connection error. Please try again.</div>');
                },
                complete: function() {
                    hideLoading($btn);
                }
            });
        });

        // Load user's orders if logged in
        function loadMyOrders() {
            if ($('#my-orders').length === 0) return;

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sbha_get_my_orders',
                    nonce: config.nonce
                },
                success: function(res) {
                    if (res.success && res.data && res.data.orders) {
                        var html = '';
                        res.data.orders.forEach(function(order) {
                            html += '<div class="sh-order-card">';
                            html += '<strong>#' + order.order_number + '</strong> - ';
                            html += '<span class="sh-status sh-status-' + order.status + '">' + order.status + '</span>';
                            html += '<br><small>' + order.created_at + '</small>';
                            html += '</div>';
                        });
                        $('#my-orders').html(html || '<p>No orders yet.</p>');
                    }
                }
            });
        }

        // ==================== CONTACT FORM ====================

        $(document).on('submit', '#contact-form', function(e) {
            e.preventDefault();
            log('Contact form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            showLoading($btn);

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=sbha_contact&nonce=' + config.nonce,
                success: function(res) {
                    log('Contact response:', res);
                    var $msg = $('#contact-msg');
                    
                    if (res.success) {
                        $msg.removeClass('error').addClass('sh-message success').text('✓ Message sent! We\'ll get back to you soon.').show();
                        $form[0].reset();
                        showToast('success', 'Message sent!');
                    } else {
                        $msg.removeClass('success').addClass('sh-message error').text('✗ ' + (res.data || 'Error sending message')).show();
                    }
                },
                error: function() {
                    $('#contact-msg').addClass('sh-message error').text('✗ Connection error').show();
                },
                complete: function() {
                    hideLoading($btn);
                }
            });
        });

        // ==================== DOCUMENTS ====================

        $(document).on('submit', '#docs-form', function(e) {
            e.preventDefault();
            log('Docs form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');

            showLoading($btn);

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=sbha_get_documents&nonce=' + config.nonce,
                success: function(res) {
                    log('Docs response:', res);
                    var $results = $('#docs-results');
                    
                    if (res.success && res.data && res.data.documents && res.data.documents.length > 0) {
                        var html = '<div class="sh-docs-list">';
                        res.data.documents.forEach(function(doc) {
                            html += '<div class="sh-doc-card">';
                            html += '<div class="sh-doc-info">';
                            html += '<h4>' + doc.name + '</h4>';
                            html += '<span class="sh-doc-meta">' + doc.created_at + '</span>';
                            html += '</div>';
                            html += '<a href="' + doc.url + '" class="sh-btn sh-btn-sm sh-btn-primary" target="_blank">Download</a>';
                            html += '</div>';
                        });
                        html += '</div>';
                        $results.html(html);
                    } else {
                        $results.html('<div class="sh-message info">No documents found.</div>');
                    }
                },
                error: function() {
                    $('#docs-results').html('<div class="sh-message error">Connection error</div>');
                },
                complete: function() {
                    hideLoading($btn);
                }
            });
        });

        // ==================== LOGIN MODAL ====================

        // Open login modal
        $(document).on('click', '#login-btn, .sh-login-btn', function(e) {
            e.preventDefault();
            log('Login button clicked');
            $('#auth-modal').addClass('active');
            $('body').css('overflow', 'hidden');
            // Show login form by default
            $('.sh-auth-tab').removeClass('active');
            $('.sh-auth-tab[data-tab="login"]').addClass('active');
            $('.sh-auth-form').removeClass('active').hide();
            $('#login-form').addClass('active').show();
        });

        // Close modal
        $(document).on('click', '.sh-modal-bg, .sh-modal-close', function(e) {
            e.preventDefault();
            log('Modal close clicked');
            $('.sh-modal').removeClass('active');
            $('body').css('overflow', '');
        });

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.sh-modal').removeClass('active');
                $('body').css('overflow', '');
            }
        });

        // Auth tabs
        $(document).on('click', '.sh-auth-tab', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            log('Auth tab clicked:', tab);

            $('.sh-auth-tab').removeClass('active');
            $(this).addClass('active');

            $('.sh-auth-form').removeClass('active').hide();
            $('#' + tab + '-form').addClass('active').show();
        });

        // Login form
        $(document).on('submit', '#login-form', function(e) {
            e.preventDefault();
            log('Login form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var $msg = $('#login-msg');

            showLoading($btn);
            $msg.hide();

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=sbha_login&nonce=' + config.nonce,
                success: function(res) {
                    log('Login response:', res);
                    if (res.success) {
                        $msg.removeClass('error').addClass('sh-message success').text('✓ Success! Reloading...').show();
                        showToast('success', 'Login successful!');
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        $msg.removeClass('success').addClass('sh-message error').text('✗ ' + (res.data || 'Invalid credentials')).show();
                        hideLoading($btn);
                    }
                },
                error: function() {
                    $msg.addClass('sh-message error').text('✗ Connection error').show();
                    hideLoading($btn);
                }
            });
        });

        // Register form
        $(document).on('submit', '#register-form', function(e) {
            e.preventDefault();
            log('Register form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var $msg = $('#register-msg');

            // Check password confirmation if field exists
            var pw = $form.find('[name="password"]').val();
            var pw2 = $form.find('[name="password_confirm"]').val();
            if (pw2 && pw !== pw2) {
                $msg.addClass('sh-message error').text('✗ Passwords do not match').show();
                return;
            }

            showLoading($btn);
            $msg.hide();

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=sbha_register&nonce=' + config.nonce,
                success: function(res) {
                    log('Register response:', res);
                    if (res.success) {
                        $msg.removeClass('error').addClass('sh-message success').text('✓ Account created! Reloading...').show();
                        showToast('success', 'Registration successful!');
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        $msg.removeClass('success').addClass('sh-message error').text('✗ ' + (res.data || 'Registration failed')).show();
                        hideLoading($btn);
                    }
                },
                error: function() {
                    $msg.addClass('sh-message error').text('✗ Connection error').show();
                    hideLoading($btn);
                }
            });
        });

        // Reset password form
        $(document).on('submit', '#reset-form', function(e) {
            e.preventDefault();
            log('Reset form submitted');

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var $msg = $('#reset-msg');

            showLoading($btn);
            $msg.hide();

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=sbha_reset_password&nonce=' + config.nonce,
                success: function(res) {
                    log('Reset response:', res);
                    if (res.success) {
                        $msg.removeClass('error').addClass('sh-message success').text('✓ Password reset! Login now.').show();
                        $form[0].reset();
                        showToast('success', 'Password reset!');
                        setTimeout(function() { 
                            $('.sh-auth-tab[data-tab="login"]').trigger('click'); 
                        }, 1500);
                    } else {
                        $msg.removeClass('success').addClass('sh-message error').text('✗ ' + (res.data || 'Reset failed')).show();
                    }
                },
                error: function() {
                    $msg.addClass('sh-message error').text('✗ Connection error').show();
                },
                complete: function() {
                    hideLoading($btn);
                }
            });
        });

        // Logout
        $(document).on('click', '.sh-logout, #logout-btn', function(e) {
            e.preventDefault();
            log('Logout clicked');

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: { action: 'sbha_logout', nonce: config.nonce },
                success: function(res) {
                    if (res.success) {
                        showToast('success', 'Logged out');
                        setTimeout(function() { location.reload(); }, 1000);
                    }
                }
            });
        });

        // ==================== DROPDOWNS ====================

        // User menu toggle
        $(document).on('click', '.sh-user-btn, #user-menu-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            log('User menu clicked');
            $('.sh-dropdown, #user-dropdown').toggleClass('active');
            $('.sh-notif-panel').removeClass('active');
        });

        // Notifications toggle
        $(document).on('click', '.sh-notif-icon, #notif-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            log('Notifications clicked');
            $('.sh-notif-panel').toggleClass('active');
            $('.sh-dropdown, #user-dropdown').removeClass('active');
        });

        // Close dropdowns on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sh-user-menu, .sh-dropdown').length) {
                $('.sh-dropdown, #user-dropdown').removeClass('active');
            }
            if (!$(e.target).closest('.sh-notif-panel, .sh-notif-icon, #notif-btn').length) {
                $('.sh-notif-panel').removeClass('active');
            }
        });

        // Dropdown links with panels
        $(document).on('click', '.sh-dropdown a[data-panel], #user-dropdown a[data-panel]', function(e) {
            e.preventDefault();
            var panel = $(this).data('panel');
            log('Dropdown panel link clicked:', panel);
            if (panel) {
                switchPanel(panel);
                $('.sh-dropdown, #user-dropdown').removeClass('active');
            }
        });

        // ==================== NOTIFICATIONS ====================

        function loadNotifications() {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: { action: 'sbha_get_notifications', nonce: config.nonce },
                success: function(res) {
                    var $badge = $('#notif-badge, .sh-badge');
                    var $list = $('#notif-list');
                    
                    if (res.success && res.data && res.data.notifications && res.data.notifications.length > 0) {
                        var unread = 0;
                        var html = '';
                        
                        res.data.notifications.forEach(function(n) {
                            if (!n.is_read) unread++;
                            html += '<div class="sh-notif-item' + (n.is_read ? '' : ' unread') + '" data-id="' + n.id + '">';
                            html += '<div class="sh-notif-title">' + n.title + '</div>';
                            html += '<div class="sh-notif-msg">' + n.message + '</div>';
                            html += '<div class="sh-notif-time">' + n.created_at + '</div>';
                            html += '</div>';
                        });
                        
                        $list.html(html);
                        
                        if (unread > 0) {
                            $badge.text(unread).show();
                        } else {
                            $badge.hide();
                        }
                    } else {
                        $badge.hide();
                        $list.html('<div class="sh-notif-item"><div class="sh-notif-msg">No notifications</div></div>');
                    }
                },
                error: function() {
                    $('#notif-badge, .sh-badge').hide();
                }
            });
        }

        // Mark notification as read
        $(document).on('click', '.sh-notif-item', function() {
            var id = $(this).data('id');
            if (id) {
                $(this).removeClass('unread');
                $.ajax({
                    url: config.ajaxUrl,
                    type: 'POST',
                    data: { action: 'sbha_mark_read', notification_id: id, nonce: config.nonce }
                });
            }
        });

        // ==================== INITIALIZATION ====================

        // Initialize quote preview
        updateQuotePreview();

        // Load notifications
        loadNotifications();

        // Load user orders if available
        loadMyOrders();

        // Ensure first panel is active
        if (!$('.sh-panel.active').length) {
            $('#panel-services').addClass('active');
        }

        // Debug: Log all bound handlers
        log('✅ All event handlers bound successfully');
        log('App container found:', $app.length > 0);
        log('Nav buttons found:', $('.sh-nav-btn').length);
        log('Panels found:', $('.sh-panel').length);
        log('Active panel:', $('.sh-panel.active').attr('id'));

    }); // End document ready

})(jQuery);
