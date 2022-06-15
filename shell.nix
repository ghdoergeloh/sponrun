{ pkgs ? import <nixpkgs> {} }:
pkgs.mkShell {
  buildInputs = [
    pkgs.php74
    pkgs.php74Packages.composer
  ];
}