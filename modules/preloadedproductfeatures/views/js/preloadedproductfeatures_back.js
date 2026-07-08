document.addEventListener("DOMContentLoaded", function () {
    const addFeatureButton = document.getElementById("product_details_features_add_feature");

    if (!addFeatureButton) return;

    // Create new button
    const defaultButton = document.createElement("button");
    defaultButton.type = "button";
    defaultButton.className = addFeatureButton.className;
    defaultButton.style.cssText = "margin-right:20px;";
    defaultButton.innerHTML = `
        <i class="material-icons">playlist_add</i>
        <span class="btn-label">Add default features</span>
    `;

    // Insert before existing button
    addFeatureButton.parentNode.insertBefore(defaultButton, addFeatureButton);

    // Click handler
    defaultButton.addEventListener("click", function () {
        for (let i = 0; i < 10; i++) {
            addFeatureButton.click();
        }

        const featureIds = [8, 9, 11, 13, 14, 15, 16, 1, 17, 19];

        setTimeout(function () {
            document.querySelectorAll(".form-group.row.product-feature select.feature-selector").forEach(function (select, i) {
                if (featureIds[i] !== undefined) {
                    $(select).val(String(featureIds[i])).trigger("change");
                }
            });
        }, 500);
    });
});

// 8 Title
// 9 Author
// 11 Checking and Preparation
// 13 Volumes
// 14 Pages
// 15 Edition
// 16 Cover
// 1 Format
// 17 Harakat
// 19 Print Quality
