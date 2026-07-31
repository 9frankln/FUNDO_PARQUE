<div x-data="{ showPassword: false, showConfirmation: false }"
     x-init="document.body.classList.add('overflow-hidden'); return () => document.body.classList.remove('overflow-hidden')"
     class="agro-dialog-overlay">
    <section role="dialog" aria-modal="true" aria-label="Asignar nueva contraseña" class="agro-dialog agro-dialog--compact">
        <header class="flex items-start justify-between border-b border-zinc-200 p-4 dark:border-zinc-800 sm:px-6">
            <div><p class="agro-kicker">Seguridad de cuenta</p><h3 class="mt-1 text-xl font-extrabold">Nueva contraseña</h3><p class="mt-1 text-xs text-zinc-500">Usuario: <?php echo e($passwordResetUserName); ?></p></div>
            <button wire:click="closePasswordResetModal" class="agro-icon-button !h-9 !w-9" aria-label="Cerrar">&times;</button>
        </header>
        <div class="space-y-4 p-4 sm:p-6">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-100">Solo administrador puede realizar esta acción. Al guardar, sesiones activas del usuario serán cerradas.</div>
            <label class="block"><span class="mb-1.5 block text-xs font-bold">Contraseña nueva</span><div class="relative"><input wire:model="newPassword" x-bind:type="showPassword ? 'text' : 'password'" autocomplete="new-password" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 pr-11 text-sm dark:border-zinc-700"><button type="button" x-on:click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-500" x-text="showPassword ? 'Ocultar' : 'Ver'"></button></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
            <label class="block"><span class="mb-1.5 block text-xs font-bold">Confirmar contraseña</span><div class="relative"><input wire:model="newPasswordConfirmation" x-bind:type="showConfirmation ? 'text' : 'password'" autocomplete="new-password" class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 pr-11 text-sm dark:border-zinc-700"><button type="button" x-on:click="showConfirmation = !showConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-500" x-text="showConfirmation ? 'Ocultar' : 'Ver'"></button></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPasswordConfirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
        </div>
        <footer class="flex flex-col-reverse gap-2 border-t border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:justify-end sm:px-6"><button wire:click="closePasswordResetModal" wire:loading.attr="disabled" wire:target="resetUserPassword" class="agro-button-secondary">Cancelar</button><button wire:click="resetUserPassword" wire:loading.attr="disabled" wire:target="resetUserPassword" class="agro-button"><span wire:loading.remove wire:target="resetUserPassword">Guardar contraseña</span><span wire:loading wire:target="resetUserPassword">Guardando...</span></button></footer>
    </section>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/livewire/ajustes/password-reset.blade.php ENDPATH**/ ?>