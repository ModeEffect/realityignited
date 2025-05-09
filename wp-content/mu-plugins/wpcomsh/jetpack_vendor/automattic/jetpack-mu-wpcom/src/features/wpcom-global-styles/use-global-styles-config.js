import { useSelect } from '@wordpress/data';

/**
 * Custom hook to detect whether Global Styles are in use.
 *
 * @return {object} An object containing globalStylesInUse and globalStylesId.
 */
export function useGlobalStylesConfig() {
	return useSelect( select => {
		const { getEditedEntityRecord, __experimentalGetCurrentGlobalStylesId } = select( 'core' );

		const _globalStylesId = __experimentalGetCurrentGlobalStylesId
			? __experimentalGetCurrentGlobalStylesId()
			: null;
		const globalStylesRecord = getEditedEntityRecord( 'root', 'globalStyles', _globalStylesId );

		const globalStylesConfig = {
			styles: globalStylesRecord?.styles ?? {},
			settings: globalStylesRecord?.settings ?? {},
		};

		// Determine if the global Styles are in use on the current site.
		const globalStylesInUse = !! (
			Object.keys( globalStylesConfig.styles ).length ||
			Object.keys( globalStylesConfig.settings ).length
		);

		return {
			globalStylesInUse,
			globalStylesId: _globalStylesId,
		};
	}, [] );
}
