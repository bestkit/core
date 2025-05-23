import app from '../../common/app';
import Component from '../Component';
import Alert, { AlertAttrs } from './Alert';
import Button from './Button';

import type Mithril from 'mithril';
import type ModalManagerState from '../states/ModalManagerState';
import type RequestError from '../utils/RequestError';
import type ModalManager from './ModalManager';
import fireDebugWarning from '../helpers/fireDebugWarning';
import classList from '../utils/classList';

export interface IInternalModalAttrs {
  state: ModalManagerState;
  animateShow: ModalManager['animateShow'];
  animateHide: ModalManager['animateHide'];
}

export interface IDismissibleOptions {
  isDismissible: boolean;
  viaCloseButton: boolean;
  viaEscKey: boolean;
  viaBackdropClick: boolean;
}

/**
 * The `Modal` component displays a modal dialog, wrapped in a form. Subclasses
 * should implement the `className`, `title`, and `content` methods.
 */
export default abstract class Modal<ModalAttrs extends IInternalModalAttrs = IInternalModalAttrs, CustomState = undefined> extends Component<
  ModalAttrs,
  CustomState
> {

  static readonly isDismissible: boolean = true;

  /**
   * Can the model be dismissed with a close button (X)?
   *
   * If `false`, no close button is shown.
   */
  protected static readonly isDismissibleViaCloseButton: boolean = true;
  /**
   * Can the modal be dismissed by pressing the Esc key on a keyboard?
   */
  protected static readonly isDismissibleViaEscKey: boolean = true;
  /**
   * Can the modal be dismissed via a click on the backdrop.
   */
  protected static readonly isDismissibleViaBackdropClick: boolean = true;

  static get dismissibleOptions(): IDismissibleOptions {
    // If someone sets this to `false`, provide the same behaviour as previous versions of Bestkit.
    if (!this.isDismissible) {
      return {
        isDismissible: false,
        viaCloseButton: false,
        viaEscKey: false,
        viaBackdropClick: false,
      };
    }

    return {
      isDismissible: true,
      viaCloseButton: this.isDismissibleViaCloseButton,
      viaEscKey: this.isDismissibleViaEscKey,
      viaBackdropClick: this.isDismissibleViaBackdropClick,
    };
  }

  protected loading: boolean = false;

  /**
   * Attributes for an alert component to show below the header.
   */
  alertAttrs: AlertAttrs | null = null;

  oninit(vnode: Mithril.Vnode<ModalAttrs, this>) {
    super.oninit(vnode);

    const missingMethods: string[] = [];

    ['className', 'title', 'content', 'onsubmit'].forEach((method) => {
      if (!(this as any)[method]) {
        (this as any)[method] = function (): void {};
        missingMethods.push(method);
      }
    });

    if (missingMethods.length > 0) {
      fireDebugWarning(
        `Modal \`${this.constructor.name}\` does not implement all abstract methods of the Modal super class. Missing methods: ${missingMethods.join(
          ', '
        )}.`
      );
    }
  }

  oncreate(vnode: Mithril.VnodeDOM<ModalAttrs, this>) {
    super.oncreate(vnode);

    this.attrs.animateShow(() => this.onready());
  }

  onbeforeremove(vnode: Mithril.VnodeDOM<ModalAttrs, this>): Promise<void> | void {
    super.onbeforeremove(vnode);

    // If the global modal state currently contains a modal,
    // we've just opened up a new one, and accordingly,
    // we don't need to show a hide animation.
    if (!this.attrs.state.modal) {
      // Here, we ensure that the animation has time to complete.
      // See https://mithril.js.org/lifecycle-methods.html#onbeforeremove
      // Bootstrap's Modal.TRANSITION_DURATION is 300 ms.
      return new Promise((resolve) => setTimeout(resolve, 300));
    }
  }

  /**
   * @todo split into FormModal and Modal in 2.0
   */
  view() {
    if (this.alertAttrs) {
      this.alertAttrs.dismissible = false;
    }

    return (
      <div className={classList('Modal modal-dialog fade', this.className())}>
        <div className="Modal-content">
          {this.dismissibleOptions.viaCloseButton && (
            <div className="Modal-close App-backControl">
              <Button
                icon="fas fa-times"
                onclick={() => this.hide()}
                className="Button Button--icon Button--link"
                aria-label={app.translator.trans('core.lib.modal.close')}
              />
            </div>
          )}

          <form onsubmit={this.onsubmit.bind(this)}>
            <div className="Modal-header">
              <h3 className="App-titleControl App-titleControl--text">{this.title()}</h3>
            </div>

            {!!this.alertAttrs && (
              <div className="Modal-alert">
                <Alert {...this.alertAttrs} />
              </div>
            )}

            {this.content()}
          </form>
        </div>
      </div>
    );
  }

  /**
   * Get the class name to apply to the modal.
   */
  abstract className(): string;

  /**
   * Get the title of the modal dialog.
   */
  abstract title(): Mithril.Children;

  /**
   * Get the content of the modal.
   */
  abstract content(): Mithril.Children;

  /**
   * Handle the modal form's submit event.
   */
  onsubmit(e: SubmitEvent): void {
    // ...
  }

  /**
   * Callback executed when the modal is shown and ready to be interacted with.
   *
   * @remark Focuses the first input in the modal.
   */
  onready(): void {
    this.$().find('input, select, textarea').first().trigger('focus').trigger('select');
  }

  /**
   * Hides the modal.
   */
  hide(): void {
    this.attrs.animateHide();
  }

  /**
   * Sets `loading` to false and triggers a redraw.
   */
  loaded(): void {
    this.loading = false;
    m.redraw();
  }

  /**
   * Shows an alert describing an error returned from the API, and gives focus to
   * the first relevant field involved in the error.
   */
  onerror(error: RequestError): void {
    this.alertAttrs = error.alert;

    m.redraw();

    if (error.status === 422 && error.response?.errors) {
      this.$('form [name=' + (error.response.errors as any[])[0].source.pointer.replace('/data/attributes/', '') + ']').trigger('select');
    } else {
      this.onready();
    }
  }

  private get dismissibleOptions(): IDismissibleOptions {
    return (this.constructor as typeof Modal).dismissibleOptions;
  }
}
