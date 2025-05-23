import app from '../../admin/app';

export default function getCategorizedExtensions() {
  let extensions = {};

  Object.keys(app.data.extensions).map((id) => {
    const extension = app.data.extensions[id];
    let category = extension.extra['bestkit-extension'].category;

    // Wrap languages packs into new system
    if (extension.extra['bestkit-locale']) {
      category = 'language';
    }

    if (category in app.extensionCategories) {
      extensions[category] = extensions[category] || [];

      extensions[category].push(extension);
    } else {
      extensions.feature = extensions.feature || [];

      extensions.feature.push(extension);
    }
  });

  return extensions;
}
