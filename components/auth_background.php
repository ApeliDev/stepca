<?php
/**
 * Auth Background Component
 * Renders the animated background for authentication pages
 * 
 * @param string $variant - 'login' or 'register' to adjust icon density
 */
function renderAuthBackground($variant = 'login') {
    $isRegister = $variant === 'register';
    $opacity = $isRegister ? 'opacity-8' : 'opacity-10';
    $iconSize = $isRegister ? 'text-xl' : 'text-2xl';
?>
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-10">
        <div class="absolute top-[<?php echo $isRegister ? '5%' : '10%'; ?>] left-[<?php echo $isRegister ? '8%' : '10%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float"><i class="fas fa-coins"></i></div>
        <div class="absolute top-[<?php echo $isRegister ? '15%' : '20%'; ?>] right-[<?php echo $isRegister ? '12%' : '10%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 1s;"><i class="fas fa-chart-line"></i></div>
        <div class="absolute bottom-[<?php echo $isRegister ? '25%' : '30%'; ?>] left-[<?php echo $isRegister ? '10%' : '15%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 2s;"><i class="fas fa-dollar-sign"></i></div>
        <div class="absolute bottom-[<?php echo $isRegister ? '8%' : '10%'; ?>] right-[<?php echo $isRegister ? '15%' : '20%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 3s;"><i class="fas fa-piggy-bank"></i></div>
        <div class="absolute top-[<?php echo $isRegister ? '45%' : '50%'; ?>] left-[<?php echo $isRegister ? '3%' : '5%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 4s;"><i class="fas fa-gem"></i></div>
        <div class="absolute top-[<?php echo $isRegister ? '65%' : '60%'; ?>] right-[<?php echo $isRegister ? '8%' : '5%'; ?>] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 5s;"><i class="fas fa-trophy"></i></div>
        
        <?php if ($isRegister): ?>
        <div class="absolute top-[30%] right-[25%] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 2.5s;"><i class="fas fa-handshake"></i></div>
        <div class="absolute bottom-[40%] left-[20%] text-primary <?php echo $opacity; ?> <?php echo $iconSize; ?> animate-float" style="animation-delay: 1.5s;"><i class="fas fa-rocket"></i></div>
        <?php endif; ?>
    </div>
<?php
}
?>