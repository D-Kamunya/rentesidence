// DataTable initialisation for the billing center is handled entirely
// by invoice.js, which uses server-side AJAX and manages all five table
// instances. This file is intentionally commented out to prevent double-init errors.

// $(document).ready(function () {
//   $("#datatableBilling1").DataTable({
//     language: {
//       paginate: {
//         previous: "<i class='mdi mdi-chevron-left'>",
//         next: "<i class='mdi mdi-chevron-right'>",
//       },
//     },

//     pageLength: 10,
//     responsive: true,
//     order: [1, 'desc'],
//     ordering: false,
//     autoWidth:false,
//     drawCallback: function () {
//       $(".dataTables_length select").addClass("form-select form-select-sm");
//     },
//   });

//   $("#datatableBilling2").DataTable({
//     language: {
//       paginate: {
//         previous: "<i class='mdi mdi-chevron-left'>",
//         next: "<i class='mdi mdi-chevron-right'>",
//       },
//     },

//     pageLength: 10,
//     responsive: true,
//     order: [1, 'desc'],
//     ordering: false,
//     autoWidth:false,
//     drawCallback: function () {
//       $(".dataTables_length select").addClass("form-select form-select-sm");
//     },
//   });

//   $("#datatableBilling3").DataTable({
//     language: {
//       paginate: {
//         previous: "<i class='mdi mdi-chevron-left'>",
//         next: "<i class='mdi mdi-chevron-right'>",
//       },
//     },

//     pageLength: 10,
//     responsive: true,
//     order: [1, 'desc'],
//     ordering: false,
//     autoWidth:false,
//     drawCallback: function () {
//       $(".dataTables_length select").addClass("form-select form-select-sm");
//     },
//   });

//   $("#datatableBilling4").DataTable({
//     language: {
//       paginate: {
//         previous: "<i class='mdi mdi-chevron-left'>",
//         next: "<i class='mdi mdi-chevron-right'>",
//       },
//     },

//     pageLength: 10,
//     responsive: true,
//     order: [1, 'desc'],
//     ordering: false,
//     autoWidth:false,
//     drawCallback: function () {
//       $(".dataTables_length select").addClass("form-select form-select-sm");
//     },
//   });
  
// });