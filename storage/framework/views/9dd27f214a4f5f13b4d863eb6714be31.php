<?php
    $pageName = $paginator->getPageName();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasPages()): ?>
    <nav class="agro-pagination" role="navigation" aria-label="Navegación de páginas">
        <div class="flex items-center justify-between gap-3 sm:hidden">
            <button type="button" wire:click="previousPage('<?php echo e($pageName); ?>')" wire:loading.attr="disabled"
                    <?php if($paginator->onFirstPage()): echo 'disabled'; endif; ?>
                    class="agro-pagination__mobile-button">
                Anterior
            </button>
            <span class="agro-pagination__mobile-status"><?php echo e($paginator->currentPage()); ?> / <?php echo e($paginator->lastPage()); ?></span>
            <button type="button" wire:click="nextPage('<?php echo e($pageName); ?>')" wire:loading.attr="disabled"
                    <?php if(! $paginator->hasMorePages()): echo 'disabled'; endif; ?>
                    class="agro-pagination__mobile-button">
                Siguiente
            </button>
        </div>

        <div class="hidden items-center justify-between gap-6 sm:flex">
            <p class="agro-pagination__summary">
                Mostrando <strong><?php echo e($paginator->firstItem()); ?></strong> a <strong><?php echo e($paginator->lastItem()); ?></strong>
                de <strong><?php echo e($paginator->total()); ?></strong> resultados
            </p>

            <div class="inline-flex items-center gap-1" aria-label="Páginas disponibles">
                <button type="button" wire:click="previousPage('<?php echo e($pageName); ?>')" wire:loading.attr="disabled"
                        <?php if($paginator->onFirstPage()): echo 'disabled'; endif; ?>
                        class="agro-pagination__button" aria-label="Página anterior">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
                </button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_string($element)): ?>
                        <span class="agro-pagination__ellipsis"><?php echo e($element); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($element)): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $paginator->currentPage()): ?>
                                <span class="agro-pagination__button agro-pagination__button--active" aria-current="page"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <button type="button" wire:key="paginator-<?php echo e($pageName); ?>-<?php echo e($page); ?>"
                                        wire:click="gotoPage(<?php echo e($page); ?>, '<?php echo e($pageName); ?>')"
                                        class="agro-pagination__button" aria-label="Ir a la página <?php echo e($page); ?>">
                                    <?php echo e($page); ?>

                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button type="button" wire:click="nextPage('<?php echo e($pageName); ?>')" wire:loading.attr="disabled"
                        <?php if(! $paginator->hasMorePages()): echo 'disabled'; endif; ?>
                        class="agro-pagination__button" aria-label="Página siguiente">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                </button>
            </div>
        </div>
    </nav>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/pagination.blade.php ENDPATH**/ ?>