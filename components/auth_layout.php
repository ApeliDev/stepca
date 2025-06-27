<?php
/**
 * Auth Layout Component
 * Provides the main layout structure for authentication pages
 * 
 * @param string $containerClass - Additional CSS classes for the container
 * @param string $cardClass - Additional CSS classes for the card
 */
function startAuthLayout($containerClass = 'max-w-md', $cardClass = '') {
?>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-darkest min-h-screen flex items-center justify-center p-4">
    
    <!-- Main Container -->
    <div class="w-full <?php echo $containerClass; ?> mx-auto relative z-20">
        <!-- Auth Card -->
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-primary/20 overflow-hidden <?php echo $cardClass; ?>">
<?php
}

function endAuthLayout($jsFile = null, $additionalFooter = null) {
?>
        </div>
        
        <?php if ($additionalFooter): ?>
        <?php echo $additionalFooter; ?>
        <?php else: ?>
        <!-- Security Badge -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
                <i class="fas fa-shield-alt text-primary mr-2"></i>
                Protected by 256-bit SSL encryption
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($jsFile): ?>
    <script src="<?php echo $jsFile; ?>"></script>
    <?php endif; ?>
</body>
</html>
<?php
}
?>