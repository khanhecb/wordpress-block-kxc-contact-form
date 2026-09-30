(function () {
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const ServerSideRender = wp.serverSideRender;

    registerBlockType('site-core/contact-form', {
        edit() {
            return createElement(ServerSideRender, {
                block: 'site-core/contact-form',
            });
        },
        save() {
            return null;
        },
    });
})();