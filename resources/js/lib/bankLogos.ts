// Mapping of bank names to SVG file paths
const bankLogoMap: Record<string, { svgFile: string | null; color: string }> = {
  'Nubank': { svgFile: 'Nu Pagamentos S.A/nubank-branco.svg', color: '#8B10AE' },
  'Nu Pagamentos S.A': { svgFile: 'Nu Pagamentos S.A/nubank-branco.svg', color: '#8B10AE' },
  'Banco Inter S.A': { svgFile: 'Banco Inter S.A/inter.svg', color: '#FF7A00' },
  'Banco Inter': { svgFile: 'Banco Inter S.A/inter.svg', color: '#FF7A00' },
  'Inter': { svgFile: 'Banco Inter S.A/inter.svg', color: '#FF7A00' },
  'Itaú': { svgFile: null, color: '#EC7000' },
  'Itaú Unibanco': { svgFile: null, color: '#EC7000' },
  'Bradesco': { svgFile: 'Bradesco S.A/bradesco com nome.svg', color: '#CC092F' },
  'Bradesco S.A': { svgFile: 'Bradesco S.A/bradesco com nome.svg', color: '#CC092F' },
  'Banco do Brasil': { svgFile: 'Banco do Brasil S.A/banco-do-brasil-com-fundo.svg', color: '#FFDD00' },
  'Banco do Brasil S.A': { svgFile: 'Banco do Brasil S.A/banco-do-brasil-com-fundo.svg', color: '#FFDD00' },
  'Caixa': { svgFile: 'Caixa Econômica Federal/caixa-economica-federal-1.svg', color: '#0066B3' },
  'Caixa Econômica Federal': { svgFile: 'Caixa Econômica Federal/caixa-economica-federal-1.svg', color: '#0066B3' },
  'Santander': { svgFile: 'Banco Santander Brasil S.A/banco-santander-logo.svg', color: '#EC0000' },
  'Banco Santander Brasil S.A': { svgFile: 'Banco Santander Brasil S.A/banco-santander-logo.svg', color: '#EC0000' },
  'Banco Santander': { svgFile: 'Banco Santander Brasil S.A/banco-santander-logo.svg', color: '#EC0000' },
  'C6 Bank': { svgFile: 'Banco C6 S.A/c6 bank- branco.svg', color: '#000000' },
  'Banco C6 S.A': { svgFile: 'Banco C6 S.A/c6 bank- branco.svg', color: '#000000' },
  'PicPay': { svgFile: 'PicPay/Logo-PicPay -nome .svg', color: '#21C25E' },
  'Neon': { svgFile: 'Neon/header-logo-neon.svg', color: '#00D9E1' },
  'Banco Safra S.A': { svgFile: 'Banco Safra S.A/logo-safra-nome.svg', color: '#EC7000' },
  'Banco Votorantim': { svgFile: 'Banco Votorantim/banco-bv-logo.svg', color: '#FFD700' },
  'Banco BTG Pacutal': { svgFile: 'Banco BTG Pacutal/btg-pactual-nome .svg', color: '#8B10AE' },
  'Banco Original S.A': { svgFile: 'Banco Original S.A/banco-original-logo-branco-nome.svg', color: '#7D3FF2' },
  'Banco Sofisa': { svgFile: 'Banco Sofisa/logo-banco-sofisa-verde.svg', color: '#FFB81C' },
  'Banco Mercantil do Brasil S.A': { svgFile: 'Banco Mercantil do Brasil S.A/banco-mercantil-novo-azul.svg', color: '#EA5F1A' },
  'Banco Daycoval': { svgFile: 'Banco Daycoval/logo-Daycoval- maior.svg', color: '#0066CC' },
  'Banco Paulista': { svgFile: 'Banco Paulista/banco-paulista-nome.svg', color: '#00AA00' },
  'BRB - Banco de Brasilia': { svgFile: 'BRB - Banco de Brasilia/brb-logo-abreviado.svg', color: '#0051BA' },
  'Banco da Amazônia S.A': { svgFile: 'Banco da Amazônia S.A/banco-da-amazonia.svg', color: '#009639' },
  'Banco do Nordeste do Brasil S.A': { svgFile: 'Banco do Nordeste do Brasil S.A/Logo_BNB.svg', color: '#E71930' },
};

export const getBankLogo = (bankName: string | null | undefined): { svgFile: string | null; color: string } | null => {
  if (!bankName) return null;
  return bankLogoMap[bankName] || null;
};

export const getBankSvgPath = (bankName: string | null | undefined): string | null => {
  if (!bankName) return null;
  const logo = bankLogoMap[bankName];
  return logo?.svgFile || null;
};

export const getBankColor = (bankName: string | null | undefined): string | null => {
  if (!bankName) return null;
  const logo = bankLogoMap[bankName];
  return logo?.color || null;
};
