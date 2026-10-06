import StickyElementView from 'orofrontend/default/js/app/views/sticky-element-view';

describe('orofrontend/default/js/app/views/sticky-element-view', () => {
    const SCROLL_DIRECTION_DEBOUNCE = StickyElementView.prototype.CHANGE_SCROLL_DIR_TIMEOUT;

    let element;
    let view;

    function waitForScrollDirectionDebounce() {
        return new Promise(resolve => setTimeout(resolve, SCROLL_DIRECTION_DEBOUNCE + 50));
    }

    beforeEach(() => {
        element = document.createElement('div');
        document.body.appendChild(element);
        view = new StickyElementView({el: element});
    });

    afterEach(() => {
        view.dispose();
        element.remove();
    });

    it('marks the element with the scroll direction once the scrolling settles', async () => {
        view.onChangeDirection(1);
        await waitForScrollDirectionDebounce();

        expect(element.classList.contains('scroll-down')).toBeTrue();
    });

    it('drops the scroll direction change still pending when the view is disposed', async () => {
        await jasmine.spyOnGlobalErrorsAsync(async globalErrorSpy => {
            view.onChangeDirection(1);
            view.dispose();
            await waitForScrollDirectionDebounce();

            expect(globalErrorSpy).not.toHaveBeenCalled();
        });
    });
});
