import type { TransactionModalPayload } from '@/Components/TransactionModal.vue';
import { requestJson } from '@/lib/kitamoApi';

export const buildTransactionRequest = (payload: TransactionModalPayload) => ({
    kind: payload.kind,
    amount: payload.amount,
    description: payload.description,
    category: payload.category,
    account: payload.account,
    dateKind: payload.dateKind,
    dateOther: payload.dateOther,
    isPaid: payload.isPaid,
    isInstallment: payload.isInstallment,
    installmentCount: payload.installmentCount,
    tags: payload.tags ?? [],
    remove_receipt: payload.removeReceipt ?? false,
    repetir: payload.repetir ?? false,
    repetir_vezes: payload.repetir ? payload.repetirVezes ?? null : null,
    repetir_meses: payload.repetir ? payload.repetirMeses ?? null : null,
    despesa_fixa: payload.despesaFixa ?? false,
    editar_escopo: payload.editarEscopo ?? null,
});

export const hasTransactionReceipt = (payload: TransactionModalPayload) => Boolean(payload.receiptFile);

export const buildTransactionFormData = (payload: TransactionModalPayload) => {
    const data = new FormData();

    data.set('kind', payload.kind);
    data.set('amount', String(payload.amount));
    data.set('description', payload.description);
    data.set('category', payload.category ?? '');
    data.set('account', payload.account ?? '');
    data.set('dateKind', payload.dateKind);
    if (payload.dateOther) data.set('dateOther', payload.dateOther);

    data.set('isPaid', payload.isPaid ? '1' : '0');
    data.set('isInstallment', payload.isInstallment ? '1' : '0');
    data.set('installmentCount', String(payload.installmentCount ?? 1));

    data.set('repetir', payload.repetir ? '1' : '0');
    if (payload.repetir && payload.repetirVezes != null) data.set('repetir_vezes', String(payload.repetirVezes));
    if (payload.repetir && payload.repetirMeses != null) data.set('repetir_meses', String(payload.repetirMeses));
    data.set('despesa_fixa', payload.despesaFixa ? '1' : '0');
    if (payload.editarEscopo) data.set('editar_escopo', payload.editarEscopo);

    data.set('remove_receipt', payload.removeReceipt ? '1' : '0');

    for (const tag of payload.tags ?? []) {
        data.append('tags[]', tag);
    }

    if (payload.receiptFile) {
        data.set('receipt', payload.receiptFile);
    }

    return data;
};

export const executeTransfer = async (payload: TransactionModalPayload) => {
    const origem = (payload.transferFrom ?? '').trim();
    const destino = (payload.transferTo ?? '').trim();
    if (!origem || !destino) {
        throw new Error('Conta de origem/destino inválida.');
    }
    if (origem === destino) {
        throw new Error('Conta de origem e destino devem ser diferentes.');
    }

    const descricao = (payload.transferDescription ?? '').trim() || 'Transferência';

    return requestJson('/api/transferencias', {
        method: 'POST',
        body: JSON.stringify({
            conta_origem: origem,
            conta_destino: destino,
            valor: payload.amount,
            descricao,
        }),
    });
};
